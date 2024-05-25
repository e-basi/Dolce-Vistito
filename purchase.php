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
        } else {
            echo "Successfully connected with the local database<br>";

        }
        if (isset($_POST['addTransaction'])) {
            // Extracting transaction data
            $transactionID = $_POST['transactionID'];
            $transactionDate = $_POST['transactionDate'];
            $customerID = $_POST['customerID'];
            $employeeID = $_POST['employeeID'];
        
            // SQL to insert new transaction
            $insertTransactionSQL = "INSERT INTO TRANSACTION (TRANSACTIONID, TRANSACTIONDATE, TOTALAMOUNT, ISRETURNED,CUSTOMERID, EMPLOYEEID) VALUES (:transactionID, TO_DATE(:transactionDate, 'YYYY-MM-DD'),0,0, :customerID, :employeeID)";
        
            $stmtTransaction = oci_parse($conn_local, $insertTransactionSQL);
        
            // Bind variables
            oci_bind_by_name($stmtTransaction, ':transactionID', $transactionID);
            oci_bind_by_name($stmtTransaction, ':transactionDate', $transactionDate);
            oci_bind_by_name($stmtTransaction, ':customerID', $customerID);
            oci_bind_by_name($stmtTransaction, ':employeeID', $employeeID);
        
            // Execute the statement
            $rTransaction = oci_execute($stmtTransaction);
        
            // Check result and give feedback
            if ($rTransaction) {
                echo "<p>Transaction added successfully.</p>";
            } else {
                $e = oci_error($stmtTransaction);
                echo "<p>Error adding transaction: " . $e['message'] . "</p>";
            }
        }
        
           if (isset($_POST['addTransactionLine'])) {
    // Extracting transaction line data
    $transactionLineID = $_POST['transactionLineID'];
    $transactionID = $_POST['transactionID'];
    $productID = $_POST['productID'];
    $quantity = $_POST['quantity'];

    // SQL to insert new transaction line
    $insertTransactionLineSQL = "INSERT INTO TRANSACTION_LINE (TRANSACTIONLINEID, TRANSACTIONID, PRODUCTID, QUANTITY) VALUES (:transactionLineID, :transactionID, :productID, :quantity)";

    $stmtTransactionLine = oci_parse($conn_local, $insertTransactionLineSQL);

    // Bind variables
    oci_bind_by_name($stmtTransactionLine, ':transactionLineID', $transactionLineID);
    oci_bind_by_name($stmtTransactionLine, ':transactionID', $transactionID);
    oci_bind_by_name($stmtTransactionLine, ':productID', $productID);
    oci_bind_by_name($stmtTransactionLine, ':quantity', $quantity);

    // Execute the statement
    $rTransactionLine = oci_execute($stmtTransactionLine);

    // Check result and give feedback
    if ($rTransactionLine) {
        echo "<p>Transaction line added successfully.</p>";
    } else {
        $e = oci_error($stmtTransactionLine);
        echo "<p>Error adding transaction line: " . $e['message'] . "</p>";
    }
}

        
            
            
            
                oci_close($conn_local);
            
    ?>
    
    </div>       
    <div class="container">
    
        <div>
        <h2>Add New Transaction</h2>
        <form action="" method="post">
            <label for="transactionID">Transaction ID:</label>
            <input type="number" id="transactionID" name="transactionID" required><br>

            <label for="transactionDate">Transaction Date:</label>
            <input type="date" id="transactionDate" name="transactionDate" required><br>

            <label for="customerID">Customer ID:</label>
            <input type="number" id="customerID" name="customerID" required><br>

            <label for="employeeID">Employee ID:</label>
            <input type="number" id="employeeID" name="employeeID" required><br>

            <input type="submit" name="addTransaction" value="Add Transaction">
        </form>
    </div>
    <div>
            <h2>Add Transaction Line</h2>
            <form action="" method="post">
                <label for="transactionLineID">Transaction Line ID:</label>
                <input type="number" id="transactionLineID" name="transactionLineID" required><br>

                <label for="transactionID">Transaction ID:</label>
                <input type="number" id="transactionID" name="transactionID" required><br>

                <label for="productID">Product ID:</label>
                <input type="number" id="productID" name="productID" required><br>

                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" required><br>

                <input type="submit" name="addTransactionLine" value="Add Transaction Line">
            </form>
        </div>



    </div>
    
</body>
</html>