<?php 
session_start();
include('includes/config.php');

// Session Check
if(strlen($_SESSION['aid'])==0) {
    header('location:index.php');
    exit();
}

// Check product ID
if (!isset($_GET['pid']) || !is_numeric($_GET['pid'])) {
    echo "<script>alert('Invalid product ID');window.location='manage_products.php';</script>";
    exit();
}

$pid = intval($_GET['pid']);

// Fetch existing product details
$stmt = $con->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $pid);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    echo "<script>alert('Product not found');window.location='manage_products.php';</script>";
    exit();
}

// Update on POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];

    $stmt = $con->prepare("UPDATE products SET name = ?, price = ?, category_id = ? WHERE id = ?");
    $stmt->bind_param("sdii", $name, $price, $category_id, $pid);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Product updated successfully.'); window.location='manage_products.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Billing System | Edit Product</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Styles -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

<?php include_once("includes/navbar.php"); ?>
<?php include_once("includes/sidebar.php"); ?>

  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6"><h1>Edit Product</h1></div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
              <li class="breadcrumb-item active">Edit Product</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-8">
            <div class="card card-primary">
              <div class="card-header"><h3 class="card-title">Update Info</h3></div>

              <form method="post">
                <div class="card-body">
                  <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" class="form-control" name="name" required value="<?= htmlspecialchars($product['name']) ?>">
                  </div>

                  <div class="form-group">
                    <label for="price">Price (₹)</label>
                    <input type="number" class="form-control" name="price" step="0.01" required value="<?= htmlspecialchars($product['price']) ?>">
                  </div>

                  <div class="form-group">
                    <label for="category_id">Category (with Tax %)</label>
                    <select name="category_id" class="form-control" required>
                      <option value="">-- Select Category --</option>
                      <?php 
                      $categories = $con->query("SELECT * FROM categories");
                      while($c = $categories->fetch_assoc()) { 
                          $selected = ($product['category_id'] == $c['id']) ? 'selected' : '';
                          echo "<option value='{$c['id']}' $selected>{$c['category_name']} (Tax: {$c['tax_rate']}%)</option>";
                      } ?>
                    </select>
                  </div>
                </div>

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">Update Product</button>
                </div>
              </form>
            </div>
          </div> <!-- col -->
        </div> <!-- row -->
      </div> <!-- container -->
    </section>
  </div>

<?php include_once("includes/footer.php"); ?>

</div> <!-- wrapper -->

<!-- Scripts -->
<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>

</body>
</html>
