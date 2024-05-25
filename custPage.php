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

            if (isset($_POST['searchCustomerById'])) {
                $customerId = $_POST['customerId'];
            
                // Prepare the SQL query
                $query = "SELECT * FROM CUSTOMER WHERE CUSTOMERID = :customerId";
            
                $stid = oci_parse($conn_local, $query);
            
                // Bind the customer ID
                oci_bind_by_name($stid, ':customerId', $customerId);
            
                oci_execute($stid);
            
                // Fetch the results
                echo "<table border='1'>";
                echo "<tr><th>ID</th><th>First Name</th><th>Last Name</th><th>Phone</th><th>Email</th></tr>";
                while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['CUSTOMERID']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['FIRSTNAME']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['LASTNAME']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['PHONE']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['EMAIL']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            
            
            if (isset($_POST['addCustomer'])) {
                $customerId = $_POST['customerId'];
                $firstName = $_POST['firstName'];
                $lastName = $_POST['lastName'];
                $phone = $_POST['phone'];
                $email = $_POST['email'];
            
                // Prepare the SQL statement
                $query = "INSERT INTO CUSTOMER (CUSTOMERID, FIRSTNAME, LASTNAME, PHONE, EMAIL, TOTAL_PURCHASES) 
                          VALUES (:customerId, :firstName, :lastName, :phone, :email, 0)";
            
                // Prepare Oracle statement
                $stid = oci_parse($conn_local, $query);
            
                // Bind the data
                oci_bind_by_name($stid, ':customerId', $customerId);
                oci_bind_by_name($stid, ':firstName', $firstName);
                oci_bind_by_name($stid, ':lastName', $lastName);
                oci_bind_by_name($stid, ':phone', $phone);
                oci_bind_by_name($stid, ':email', $email);
            
                // Execute the statement
                $r = oci_execute($stid);
            
                if ($r) {
                    echo "Customer added successfully!";
                } else {
                    $e = oci_error($stid);
                    echo "Error adding new customer: " . $e['message'];
                }
            }
            
                        // Check if the search form has been submitted
            if (isset($_POST['searchForEdit'])) {
                $customerId = $_POST['editCustomerId'];

                // Fetch customer information
                $sqlCustomer = "SELECT * FROM CUSTOMER WHERE CUSTOMERID = :cid";
                $stmtCustomer = oci_parse($conn_local, $sqlCustomer);
                oci_bind_by_name($stmtCustomer, ":cid", $customerId);
                oci_execute($stmtCustomer);
                $_SESSION['customerInfo'] = oci_fetch_array($stmtCustomer, OCI_ASSOC+OCI_RETURN_NULLS);

                if (!$_SESSION['customerInfo']) {
                    echo 'No customer found with ID ' . htmlspecialchars($customerId);
                }
            }

            // Check if the update form has been submitted
            if (isset($_POST['updateCustomer'])) {
                $customerId = $_POST['customerId'];
                $firstName = $_POST['firstName'];
                $lastName = $_POST['lastName'];
                $phone = $_POST['phone'];
                $email = $_POST['email'];

                // Update customer information
                $sqlUpdateCustomer = "UPDATE CUSTOMER SET FIRSTNAME = :firstName, LASTNAME = :lastName, PHONE = :phone, EMAIL = :email WHERE CUSTOMERID = :cid";
                $stmtUpdateCustomer = oci_parse($conn_local, $sqlUpdateCustomer);
                oci_bind_by_name($stmtUpdateCustomer, ":firstName", $firstName);
                oci_bind_by_name($stmtUpdateCustomer, ":lastName", $lastName);
                oci_bind_by_name($stmtUpdateCustomer, ":phone", $phone);
                oci_bind_by_name($stmtUpdateCustomer, ":email", $email);
                oci_bind_by_name($stmtUpdateCustomer, ":cid", $customerId);
                oci_execute($stmtUpdateCustomer);

                echo "Customer information updated successfully.";

                // Clear the session variable after update
                unset($_SESSION['customerInfo']);
            }
            
           
        
            
            
            
                oci_close($conn_local);
            }
    ?>
    
    </div>       
    <div class="container">
    <form action="" method="post">
            <h2>Search Customer by ID</h2>
            <label for="customerId">Customer ID:</label>
            <input type="number" id="customerId" name="customerId" required><br><br>
            <input type="submit" name="searchCustomerById" value="Search">
        </form>


        <form action="" method="post">
            <h2>Add New Customer</h2>
            <label for="customerId">Customer ID:</label>
            <input type="number" id="customerId" name="customerId" required><br><br>

            <label for="firstName">First Name:</label>
            <input type="text" id="firstName" name="firstName" required><br><br>

            <label for="lastName">Last Name:</label>
            <input type="text" id="lastName" name="lastName" required><br><br>

            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" required><br><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br><br>

            <input type="submit" name="addCustomer" value="Add Customer">
        </form>

        <!-- Search form for editing customer -->
        <form action="" method="post">
            <h2>Search Customer by ID for Editing</h2>
            <input type="text" name="editCustomerId" placeholder="Enter Customer ID" required>
            <input type="submit" name="searchForEdit" value="Search">
        </form>

        <!-- Check if we have customer information in the session to display the edit form -->
        <?php if (isset($_SESSION['customerInfo'])): ?>
            <form action="" method="post">
                <h2>Update Customer Information</h2>
                <!-- Display non-editable customer ID -->
                Customer ID: <?php echo htmlspecialchars($_SESSION['customerInfo']['CUSTOMERID']); ?><br>

                <!-- Editable fields -->
                First Name: <input type="text" name="firstName" value="<?php echo htmlspecialchars($_SESSION['customerInfo']['FIRSTNAME']); ?>" required><br>
                Last Name: <input type="text" name="lastName" value="<?php echo htmlspecialchars($_SESSION['customerInfo']['LASTNAME']); ?>" required><br>
                Phone: <input type="tel" name="phone" value="<?php echo htmlspecialchars($_SESSION['customerInfo']['PHONE']); ?>" required><br>
                Email: <input type="email" name="email" value="<?php echo htmlspecialchars($_SESSION['customerInfo']['EMAIL']); ?>" required><br>

                <!-- Hidden field to carry customer ID -->
                <input type="hidden" name="customerId" value="<?php echo htmlspecialchars($_SESSION['customerInfo']['CUSTOMERID']); ?>">

                <input type="submit" name="updateCustomer" value="Update Customer">
            </form>
        <?php endif; ?>




    </div>
    
</body>
</html>