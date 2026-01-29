<?php 
session_start();
include('includes/config.php');

if(strlen($_SESSION['aid']) == 0) {
    header('location:index.php');
    exit();
}

// Insert bill
if(isset($_POST['submit'])) {
    $customer_name = $_POST['customer_name'];
    $total_amount = $_POST['total_amount'];

    // Insert bill into database
    $query = "INSERT INTO bills (customer_name, total_amount) VALUES (?, ?)";
    $stmt = $con->prepare($query);
    $stmt->bind_param("sd", $customer_name, $total_amount);
    $stmt->execute();
    $bill_id = $stmt->insert_id;

    // Insert bill items
    foreach ($_POST['product_id'] as $index => $pid) {
        $qty = $_POST['quantity'][$index];
        $price = $_POST['price'][$index];
        $tax = $_POST['tax'][$index];
        $total = $_POST['total'][$index];

        $stmt2 = $con->prepare("INSERT INTO bill_items (bill_id, product_id, quantity, price, tax_amount, total_amount) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("iiiddd", $bill_id, $pid, $qty, $price, $tax, $total);
        $stmt2->execute();
    }

    $msg = "Bill created successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Bill</title>
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <style>
    .table td, .table th { vertical-align: middle; }
  </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

<?php include_once("includes/navbar.php"); ?>
<?php include_once("includes/sidebar.php"); ?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h1>Create New Bill</h1>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">

      <?php if(isset($msg)): ?>
      <div class="alert alert-success"><?= $msg ?></div>
      <?php endif; ?>

      <form method="post">
        <div class="form-group">
          <label>Customer Name</label>
          <input type="text" name="customer_name" required class="form-control">
        </div>

        <table class="table table-bordered" id="productTable">
          <thead>
            <tr>
              <th>Product</th>
              <th>Price</th>
              <th>Tax %</th>
              <th>Qty</th>
              <th>Tax Amt</th>
              <th>Total</th>
              <th><button type="button" class="btn btn-sm btn-success" onclick="addRow()">+</button></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <select name="product_id[]" class="form-control" onchange="fetchProduct(this)">
                  <option value="">Select</option>
                  <?php
                  $query = "SELECT p.id, p.name, p.price, c.tax_rate FROM products p JOIN categories c ON c.id=p.category_id";
                  $res = $con->query($query);
                  while($row = $res->fetch_assoc()) {
                    echo "<option value='{$row['id']}' data-price='{$row['price']}' data-tax='{$row['tax_rate']}'>{$row['name']}</option>";
                  }
                  ?>
                </select>
              </td>
              <td><input type="number" name="price[]" class="form-control" readonly></td>
              <td><input type="number" name="tax_rate[]" class="form-control" readonly></td>
              <td><input type="number" name="quantity[]" class="form-control" value="1" onchange="updateTotal(this)"></td>
              <td><input type="number" name="tax[]" class="form-control" readonly></td>
              <td><input type="number" name="total[]" class="form-control" readonly></td>
              <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">X</button></td>
            </tr>
          </tbody>
        </table>

        <div class="form-group text-right">
          <label><strong>Grand Total: ₹</strong></label>
          <input type="text" name="total_amount" id="grandTotal" class="form-control d-inline-block" readonly style="width: 150px;">
        </div>

        <button type="submit" name="submit" class="btn btn-primary">Save Bill</button>
      </form>
    </div>
  </section>
</div>

<?php include_once("includes/footer.php"); ?>

</div>

<!-- Scripts -->
<script src="plugins/jquery/jquery.min.js"></script>
<script>
function fetchProduct(el) {
  let tr = el.closest('tr');
  let price = parseFloat(el.options[el.selectedIndex].dataset.price || 0);
  let tax = parseFloat(el.options[el.selectedIndex].dataset.tax || 0);
  tr.querySelector('[name="price[]"]').value = price.toFixed(2);
  tr.querySelector('[name="tax_rate[]"]').value = tax.toFixed(2);
  updateTotal(tr.querySelector('[name="quantity[]"]'));
}

function updateTotal(el) {
  let tr = el.closest('tr');
  let price = parseFloat(tr.querySelector('[name="price[]"]').value || 0);
  let taxRate = parseFloat(tr.querySelector('[name="tax_rate[]"]').value || 0);
  let qty = parseInt(tr.querySelector('[name="quantity[]"]').value || 1);
  let taxAmt = price * taxRate / 100 * qty;
  let total = (price * qty) + taxAmt;
  tr.querySelector('[name="tax[]"]').value = taxAmt.toFixed(2);
  tr.querySelector('[name="total[]"]').value = total.toFixed(2);
  calculateGrandTotal();
}

function calculateGrandTotal() {
  let totals = document.querySelectorAll('[name="total[]"]');
  let sum = 0;
  totals.forEach(input => sum += parseFloat(input.value || 0));
  document.getElementById('grandTotal').value = sum.toFixed(2);
}

function addRow() {
  let table = document.querySelector('#productTable tbody');
  let firstRow = table.rows[0];
  let newRow = firstRow.cloneNode(true);

  // Reset all input fields
  newRow.querySelectorAll('input').forEach(input => {
    if (input.name === "quantity[]") {
      input.value = 1;
    } else {
      input.value = '';
    }
  });

  // Reset select dropdown
  newRow.querySelector('select').selectedIndex = 0;

  table.appendChild(newRow);
}


function removeRow(btn) {
  let table = document.querySelector('#productTable tbody');
  if (table.rows.length > 1) {
    btn.closest('tr').remove();
    calculateGrandTotal();
  }
}
</script>
<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>

</body>
</html>
