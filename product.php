<!DOCTYPE html>
<html>
<head>
    <title>Database Operations</title>
    <style>
         .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
        }

        .tables-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 20px; /* Adjust the gap as needed */
        }

        .table-wrapper {
            flex: 0 1 30%; /* Adjust the width as needed, 30% for 3 in a row */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }

        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
        }

        h2, h3 {
            color: #333;
        }

        form {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }

        input[type="number"], input[type="text"], input[type="date"] {
            width: 100%;
            padding: 8px;
            margin: 10px 0;
            display: block;
        }

        input[type="submit"] {
            display: block;
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            background: #333;
            color: white;
            border: 0;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background: #555;
        }



        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #333;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
       
        .navbar {
            display: flex;
            justify-content: center; /* This will center the navigation items */
            background-color: #333;
            font-family: Arial, sans-serif;
            padding: 10px 0; /* Add some padding on top and bottom of the navbar */
        }
        .navbar a {
            float: left;
            font-size: 16px;
            color: white;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }
        .navbar a:hover {
            background-color: #ddd;
            color: black;
        }
    </style>
</head>
<body>
    <div class="navbar">
                <a href="index.php">Home Page</a>
                <a href="employeePage.php">Employee Page</a>
                <a href="custPage.php">Customer Page</a>
                <a href="product.php">Products</a>
                <a href="purchase.php">Purchase Products</a>
            </div>
            <div class="container">
    <?php
        error_reporting(E_ALL);
        ini_set('display_errors', 'On');

        // Database connection settings
        $local_host = 'oracle.scs.ryerson.ca';
        $local_port = '1521';
        $local_sid = 'orcl';
        $local_conn_string = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(Host=$local_host)(Port=$local_port))(CONNECT_DATA=(SID=$local_sid)))";

        // Connect to Local Database
        $conn_local = oci_connect('nobasi', '07251777', $local_conn_string);
        if (!$conn_local) {
            $m = oci_error();
            echo "Local Connection Error: " . $m['message'];
            exit;
        } else {
            echo "Successfully connected with the local database<br>";
        

            }

            // Handling form submissions
   
            if (isset($_POST['searchForProduct'])) {
                $productId = $_POST['productId'];
            
                // Fetch product details
                $queryProduct = "SELECT * FROM PRODUCT WHERE PRODUCTID = :productId";
                $stmtProduct = oci_parse($conn_local, $queryProduct);
                oci_bind_by_name($stmtProduct, ':productId', $productId);
                oci_execute($stmtProduct);
                $rowProduct = oci_fetch_assoc($stmtProduct);
            
                // Fetch inventory details
                $queryInventory = "SELECT * FROM INVENTORY WHERE PRODUCTID = :productId";
                $stmtInventory = oci_parse($conn_local, $queryInventory);
                oci_bind_by_name($stmtInventory, ':productId', $productId);
                oci_execute($stmtInventory);
                $rowInventory = oci_fetch_assoc($stmtInventory);
            }

            if (isset($_POST['addProductAndCategory'])) {
                // Product Information
                $productID = $_POST['productID'];
                $productSize = $_POST['productSize'];
                $price = $_POST['price'];
                $color = $_POST['color'];
                $productName = $_POST['productName'];
            
                // Category Information
                $categoryID = $_POST['categoryID'];
                $bottomwear = $_POST['bottomwear'];
                $topwear = $_POST['topwear'];
                $purse = $_POST['purse'];
                $dress = $_POST['dress'];
                $accessories = $_POST['accessories'];
            
                // SQL to insert new product
                $insertProductSQL = "INSERT INTO PRODUCT (PRODUCTID, PRODUCTSIZE, PRICE, COLOR, PRODUCTNAME) VALUES (:productID, :productSize, :price, :color, :productName)";
                // SQL to insert new category
                $insertCategorySQL = "INSERT INTO CATEGORY (CATEGORYID, BOTTOMWEAR, TOPWEAR, PURSE, DRESS, ACCESSORIES) VALUES (:categoryID, :bottomwear, :topwear, :purse, :dress, :accessories)";
                // SQL to insert into ProductCategory
                $insertProductCategorySQL = "INSERT INTO PRODUCTCATEGORY (PRODUCTID, CATEGORYID) VALUES (:productID, :categoryID)";
            
                // Execute product insertion
                $stmtProduct = oci_parse($conn_local, $insertProductSQL);
                oci_bind_by_name($stmtProduct, ':productID', $productID);
                oci_bind_by_name($stmtProduct, ':productSize', $productSize);
                oci_bind_by_name($stmtProduct, ':price', $price);
                oci_bind_by_name($stmtProduct, ':color', $color);
                oci_bind_by_name($stmtProduct, ':productName', $productName);
                
                $rProduct = oci_execute($stmtProduct);
            
                // Execute category insertion
                $stmtCategory = oci_parse($conn_local, $insertCategorySQL);
                oci_bind_by_name($stmtCategory, ':categoryID', $categoryID);
                oci_bind_by_name($stmtCategory, ':bottomwear', $bottomwear);
                oci_bind_by_name($stmtCategory, ':topwear', $topwear);
                oci_bind_by_name($stmtCategory, ':purse', $purse);
                oci_bind_by_name($stmtCategory, ':dress', $dress);
                oci_bind_by_name($stmtCategory, ':accessories', $accessories);
                
                $rCategory = oci_execute($stmtCategory);
            
                // Execute ProductCategory insertion
                $stmtProductCategory = oci_parse($conn_local, $insertProductCategorySQL);
                // Bind variables for ProductCategory
                oci_bind_by_name($stmtProductCategory, ':productID', $productID);
                oci_bind_by_name($stmtProductCategory, ':categoryID', $categoryID);
                $rProductCategory = oci_execute($stmtProductCategory);
            
                // Check results and give feedback
                if ($rProduct && $rCategory && $rProductCategory) {
                    echo "<p>Product, Category, and Product-Category link added successfully.</p>";
                } else {
                    echo "<p>Error adding product, category, and/or Product-Category link.</p>";
                }
            }
            
            if (isset($_POST['addInventory'])) {
                // Extracting form data
                $inventoryID = $_POST['inventoryID'];
                $productID = $_POST['productID'];
                $quantity = $_POST['quantity'];
            
                // SQL to insert new inventory item
                $insertInventorySQL = "INSERT INTO INVENTORY (INVENTORYID, PRODUCTID, QUANTITY) VALUES (:inventoryID, :productID, :quantity)";
            
                $stmtInventory = oci_parse($conn_local, $insertInventorySQL);
            
                // Bind variables
                oci_bind_by_name($stmtInventory, ':inventoryID', $inventoryID);
                oci_bind_by_name($stmtInventory, ':productID', $productID);
                oci_bind_by_name($stmtInventory, ':quantity', $quantity);
            
                $rInventory = oci_execute($stmtInventory);
            
                // Check result and give feedback
                if ($rInventory) {
                    echo "<p>Inventory item added successfully.</p>";
                } else {
                    $e = oci_error($stmtInventory);
                    echo "<p>Error adding inventory item: " . $e['message'] . "</p>";
                }
            }
            if (isset($_POST['deleteBoth'])) {
                $deleteProductID = $_POST['deleteProductID'];
                $deleteCategoryID = $_POST['deleteCategoryID'];
            
                // Start transaction
             
            
                // SQL to delete from ProductCategory
                $deleteProductCategorySQL = "DELETE FROM PRODUCTCATEGORY WHERE PRODUCTID = :deleteProductID OR CATEGORYID = :deleteCategoryID";
                $stmtDeleteProductCategory = oci_parse($conn_local, $deleteProductCategorySQL);
                oci_bind_by_name($stmtDeleteProductCategory, ':deleteProductID', $deleteProductID);
                oci_bind_by_name($stmtDeleteProductCategory, ':deleteCategoryID', $deleteCategoryID);
                $rDeleteProductCategory = oci_execute($stmtDeleteProductCategory, OCI_NO_AUTO_COMMIT);
            
                // SQL to delete product
                $deleteProductSQL = "DELETE FROM PRODUCT WHERE PRODUCTID = :deleteProductID";
                $stmtDeleteProduct = oci_parse($conn_local, $deleteProductSQL);
                oci_bind_by_name($stmtDeleteProduct, ':deleteProductID', $deleteProductID);
                $rDeleteProduct = oci_execute($stmtDeleteProduct, OCI_NO_AUTO_COMMIT);
            
                // SQL to delete category
                $deleteCategorySQL = "DELETE FROM CATEGORY WHERE CATEGORYID = :deleteCategoryID";
                $stmtDeleteCategory = oci_parse($conn_local, $deleteCategorySQL);
                oci_bind_by_name($stmtDeleteCategory, ':deleteCategoryID', $deleteCategoryID);
                $rDeleteCategory = oci_execute($stmtDeleteCategory, OCI_NO_AUTO_COMMIT);
            
                // Check results and give feedback
                if ($rDeleteProductCategory && $rDeleteProduct && $rDeleteCategory) {
                    oci_commit($conn_local);
                    echo "<p>Product, Category, and their associations in ProductCategory deleted successfully.</p>";
                } else {
                    oci_rollback($conn_local);
                    echo "<p>Error deleting product, category, and/or their associations.</p>";
                }
            }
            
            
            if (isset($_POST['deleteInventory'])) {
                $deleteInventoryID = $_POST['deleteInventoryID'];
            
                // SQL to delete inventory item
                $deleteInventorySQL = "DELETE FROM INVENTORY WHERE INVENTORYID = :deleteInventoryID";
            
                $stmtDeleteInventory = oci_parse($conn_local, $deleteInventorySQL);
                oci_bind_by_name($stmtDeleteInventory, ':deleteInventoryID', $deleteInventoryID);
            
                $rDeleteInventory = oci_execute($stmtDeleteInventory);
            
                if ($rDeleteInventory) {
                    echo "<p>Inventory item deleted successfully.</p>";
                } else {
                    $e = oci_error($stmtDeleteInventory);
                    echo "<p>Error deleting inventory item: " . $e['message'] . "</p>";
                }
            }
            if (isset($_POST['editInventory'])) {
                $productId = $_POST['editInventoryProductId'];
                $newQuantity = $_POST['newQuantity'];
            
                // SQL to update inventory quantity
                $updateInventorySQL = "UPDATE INVENTORY SET QUANTITY = :newQuantity WHERE PRODUCTID = :productId";
            
                $stmtUpdateInventory = oci_parse($conn_local, $updateInventorySQL);
                oci_bind_by_name($stmtUpdateInventory, ':productId', $productId);
                oci_bind_by_name($stmtUpdateInventory, ':newQuantity', $newQuantity);
            
                if (oci_execute($stmtUpdateInventory)) {
                    echo "<p>Inventory quantity updated successfully.</p>";
                } else {
                    $e = oci_error($stmtUpdateInventory);
                    echo "<p>Error updating inventory quantity: " . $e['message'] . "</p>";
                }
            }
            if (isset($_POST['editPrice'])) {
                $productId = $_POST['editPriceProductId'];
                $newPrice = $_POST['newPrice'];
            
                // SQL to update product price
                $updatePriceSQL = "UPDATE PRODUCT SET PRICE = :newPrice WHERE PRODUCTID = :productId";
            
                $stmtUpdatePrice = oci_parse($conn_local, $updatePriceSQL);
                oci_bind_by_name($stmtUpdatePrice, ':productId', $productId);
                oci_bind_by_name($stmtUpdatePrice, ':newPrice', $newPrice);
            
                if (oci_execute($stmtUpdatePrice)) {
                    echo "<p>Product price updated successfully.</p>";
                } else {
                    $e = oci_error($stmtUpdatePrice);
                    echo "<p>Error updating product price: " . $e['message'] . "</p>";
                }
            }
            
            
            
    
                oci_close($conn_local);
        
    ?>
    
    </div>       
    <div class="container">
            <!-- Form for searching a product -->
        <div class="container">
            <form action="" method="post">
                <h2>Search for Product Information</h2>
                <input type="text" name="productId" placeholder="Enter Product ID" required>
                <input type="submit" name="searchForProduct" value="Search">
            </form>
        </div>
          
        <!-- Display the product, inventory, and category information if available -->
        <?php if (isset($rowProduct)): ?>
            <div class="container">
                <h2>Product Information</h2>
                <p><strong>Product Name:</strong> <?php echo htmlspecialchars($rowProduct['PRODUCTNAME']); ?></p>
                <p><strong>Size:</strong> <?php echo htmlspecialchars($rowProduct['PRODUCTSIZE']); ?></p>
                <p><strong>Price:</strong> <?php echo htmlspecialchars($rowProduct['PRICE']); ?></p>
                <p><strong>Color:</strong> <?php echo htmlspecialchars($rowProduct['COLOR']); ?></p>
              
                <h2>Inventory Information</h2>
                <p><strong>Quantity:</strong> <?php echo htmlspecialchars($rowInventory['QUANTITY']); ?></p>
              

            </div>
        <?php endif; ?>

        <?php if (isset($categories) && count($categories) > 0): ?>
            <div class="container">
                <h2>Categories</h2>
                <ul>
                    <?php foreach ($categories as $category): ?>
                        <li><?php echo htmlspecialchars($category); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div>
            <h2>Add New Product and Category</h2>
            <form action="" method="post">
                <!-- Product Information -->
                <h3>Product Information</h3>
                <label for="productID">Product ID:</label>
                <input type="number" id="productID" name="productID" required><br>

                <label for="productSize">Size:</label>
                <input type="text" id="productSize" name="productSize" required><br>

                <label for="price">Price:</label>
                <input type="number" step="0.01" id="price" name="price" required><br>

                <label for="color">Color:</label>
                <input type="text" id="color" name="color" required><br>

                <label for="productName">Product Name:</label>
                <input type="text" id="productName" name="productName" required><br>

                <!-- Category Information -->
                <h3>Category Information</h3>
                <label for="categoryID">Category ID:</label>
                <input type="number" id="categoryID" name="categoryID" required><br>

                <input type="hidden" name="bottomwear" value="0">
                <input type="checkbox" id="bottomwear" name="bottomwear" value="1">
                <label for="bottomwear">Bottom Wear</label><br>

                <input type="hidden" name="topwear" value="0">
                <input type="checkbox" id="topwear" name="topwear" value="1">
                <label for="topwear">Top Wear</label><br>

                <input type="hidden" name="purse" value="0">
                <input type="checkbox" id="purse" name="purse" value="1">
                <label for="purse">Purse</label><br>

                <input type="hidden" name="dress" value="0">
                <input type="checkbox" id="dress" name="dress" value="1">
                <label for="dress">Dress</label><br>

                <input type="hidden" name="accessories" value="0">
                <input type="checkbox" id="accessories" name="accessories" value="1">
                <label for="accessories">Accessories</label><br>

                <input type="submit" name="addProductAndCategory" value="Add Product and Category">
            </form>
        </div>
        <div>
        <h2>Add New Inventory Item</h2>
        <form action="" method="post">
            <label for="inventoryID">Inventory ID:</label>
            <input type="number" id="inventoryID" name="inventoryID" required><br>

            <label for="productID">Product ID:</label>
            <input type="number" id="productID" name="productID" required><br>

            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" required><br>

            <input type="submit" name="addInventory" value="Add Inventory Item">
        </form>
    </div>
    <div>
        <h2>Delete Inventory Item</h2>
        <form action="" method="post">
            <label for="deleteInventoryID">Inventory ID:</label>
            <input type="number" id="deleteInventoryID" name="deleteInventoryID" required>
            <input type="submit" name="deleteInventory" value="Delete Inventory Item">
        </form>
    </div>

    <div>
        <h2>Delete Product and Category</h2>
        <form action="" method="post">
            <label for="deleteProductID">Product ID:</label>
            <input type="number" id="deleteProductID" name="deleteProductID" required><br>

            <label for="deleteCategoryID">Category ID:</label>
            <input type="number" id="deleteCategoryID" name="deleteCategoryID" required><br>

            <input type="submit" name="deleteBoth" value="Delete Both">
        </form>
    </div>
    <div>
        <h2>Edit Inventory Quantity</h2>
        <form action="" method="post">
            <label for="editInventoryProductId">Product ID:</label>
            <input type="number" id="editInventoryProductId" name="editInventoryProductId" required><br>

            <label for="newQuantity">New Quantity:</label>
            <input type="number" id="newQuantity" name="newQuantity" required><br>

            <input type="submit" name="editInventory" value="Update Inventory">
        </form>
    </div>
    <div>
        <h2>Edit Product Price</h2>
        <form action="" method="post">
            <label for="editPriceProductId">Product ID:</label>
            <input type="number" id="editPriceProductId" name="editPriceProductId" required><br>

            <label for="newPrice">New Price:</label>
            <input type="number" step="0.01" id="newPrice" name="newPrice" required><br>

            <input type="submit" name="editPrice" value="Update Price">
        </form>
    </div>




    </div>
    
</body>
</html>