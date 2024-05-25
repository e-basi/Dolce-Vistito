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

            if (isset($_POST['searchEmployee'])) {
                $employeeId = $_POST['searchEmployeeId']; // Make sure to sanitize this input to prevent SQL injection
                echo "Searching for employee with ID: " . $employeeId . "<br/>"; // Debug line to show what is being searched

                // Prepare the SQL query
                $sql = "SELECT * FROM EMPLOYEE WHERE EMPLOYEEID = :eid";
                $stmt = oci_parse($conn_local, $sql);

                // Bind the parameter
                oci_bind_by_name($stmt, ":eid", $employeeId);

                // Execute the statement
                $r = oci_execute($stmt);
                if (!$r) {
                    $e = oci_error($stmt);
                    echo "Error fetching employee: " . $e['message'];
                } else {
                    $row = oci_fetch_array($stmt, OCI_ASSOC+OCI_RETURN_NULLS);
                    if ($row) {
                        // Display the employee data
                        echo "<table border='1'>";
                        echo "<tr>";
                        foreach ($row as $column => $value) {
                            echo "<th>" . htmlspecialchars($column) . "</th>";
                        }
                        echo "</tr>";
                        echo "<tr>";
                        foreach ($row as $value) {
                            echo "<td>" . ($value !== null ? htmlspecialchars($value) : "&nbsp;") . "</td>";
                        }
                        echo "</tr>";
                        echo "</table>";
                    } else {
                        echo "No employee found with ID " . htmlspecialchars($employeeId);
                    }
                }
            }
          
 
            if (isset($_POST['addEmployee'])) {
                // Sanitize and assign input values to variables
                // Make sure to sanitize these inputs to prevent SQL injection
                $firstName = $_POST['firstName'];
                $lastName = $_POST['lastName'];
                $commissionAmount = $_POST['commissionAmount'];
                $totalHours = $_POST['totalHours'];
                $startDate = $_POST['startDate'];
                $accountNumber = $_POST['accountNumber'];
                $institutionNumber = $_POST['institutionNumber'];
                $branchNumber = $_POST['branchNumber'];
            
            
                // Get the next FINANCIALINFOID
                $sqlMaxId = "SELECT MAX(FINANCIALINFOID) AS MAX_ID FROM FINANCIAL_INFO";
                $stmtMaxId = oci_parse($conn_local, $sqlMaxId);
                oci_execute($stmtMaxId);
                $rowMaxId = oci_fetch_array($stmtMaxId, OCI_ASSOC);
                $maxId = $rowMaxId['MAX_ID'];
                $newId = $maxId + 1;
            
                // Insert financial info
                $sqlInsertFinancial = "INSERT INTO FINANCIAL_INFO (FINANCIALINFOID, ACCOUNTNUMBER, INSTITUTIONNUMBER, BRANCHNUMBER) VALUES (:financialinfoid, :accountnumber, :institutionnumber, :branchnumber)";
                $stmtInsertFinancial = oci_parse($conn_local, $sqlInsertFinancial);
                oci_bind_by_name($stmtInsertFinancial, ":financialinfoid", $newId);
                oci_bind_by_name($stmtInsertFinancial, ":accountnumber", $accountNumber);
                oci_bind_by_name($stmtInsertFinancial, ":institutionnumber", $institutionNumber);
                oci_bind_by_name($stmtInsertFinancial, ":branchnumber", $branchNumber);
                $resultFinancial = oci_execute($stmtInsertFinancial, OCI_NO_AUTO_COMMIT);
            
                if (!$resultFinancial) {
                    $e = oci_error($stmtInsertFinancial);
                    echo "Error inserting financial info: " . $e['message'];
                    oci_rollback($conn_local); // Rollback on error
                } else {
                    // Insert employee
                    $sqlInsertEmployee = "INSERT INTO EMPLOYEE (EMPLOYEEID, COMMISSIONAMOUNT, TOTALHOURS, FIRSTNAME, LASTNAME, TOTALSALE, FINANCIALINFOID, STARTDATE) VALUES (:employeeid, :commissionamount, :totalhours, :firstname, :lastname, 0, :financialinfoid, TO_DATE(:startdate, 'YYYY-MM-DD'))";
                    $stmtInsertEmployee = oci_parse($conn_local, $sqlInsertEmployee);
                    oci_bind_by_name($stmtInsertEmployee, ":employeeid", $newId);
                    oci_bind_by_name($stmtInsertEmployee, ":commissionamount", $commissionAmount);
                    oci_bind_by_name($stmtInsertEmployee, ":firstname", $firstName);
                    oci_bind_by_name($stmtInsertEmployee, ":lastname", $lastName);
                    oci_bind_by_name($stmt, ":totalhours", $totalHours);
                    oci_bind_by_name($stmtInsertEmployee, ":financialinfoid", $newId);
                    oci_bind_by_name($stmtInsertEmployee, ":startdate", $startDate);
                    
                    $resultEmployee = oci_execute($stmtInsertEmployee, OCI_NO_AUTO_COMMIT);
            
                    if (!$resultEmployee) {
                        $e = oci_error($stmtInsertEmployee);
                        echo "Error inserting new employee: " . $e['message'];
                        oci_rollback($conn_local); // Rollback on error
                    } else {
                        oci_commit($conn_local); // Commit the transaction
                        echo "Employee and Financial info added successfully with ID " . $newId;
                    }
                }
            }
            if (isset($_POST['deleteEmployee'])) {
                $employeeId = $_POST['deleteEmployeeId']; // Capture the employee ID
            
            
                // Delete employee
                $sqlDeleteEmployee = "DELETE FROM EMPLOYEE WHERE EMPLOYEEID = :employeeid";
                $stmtDeleteEmployee = oci_parse($conn_local, $sqlDeleteEmployee);
                oci_bind_by_name($stmtDeleteEmployee, ":employeeid", $employeeId);
                $resultEmployee = oci_execute($stmtDeleteEmployee, OCI_NO_AUTO_COMMIT);
            
                if (!$resultEmployee) {
                    $e = oci_error($stmtDeleteEmployee);
                    echo "Error deleting employee: " . $e['message'];
                    oci_rollback($conn_local); // Rollback on error
                } else {
                    // Delete financial info
                    $sqlDeleteFinancial = "DELETE FROM FINANCIAL_INFO WHERE FINANCIALINFOID = :financialinfoid";
                    $stmtDeleteFinancial = oci_parse($conn_local, $sqlDeleteFinancial);
                    oci_bind_by_name($stmtDeleteFinancial, ":financialinfoid", $employeeId);
                    $resultFinancial = oci_execute($stmtDeleteFinancial, OCI_NO_AUTO_COMMIT);
            
                    if (!$resultFinancial) {
                        $e = oci_error($stmtDeleteFinancial);
                        echo "Error deleting financial info: " . $e['message'];
                        oci_rollback($conn_local); // Rollback on error
                    } else {
                        oci_commit($conn_local); // Commit the transaction
                        echo "Employee and Financial info deleted successfully.";
                    }
                }
            }

            if (isset($_POST['searchForEdit'])) {
                $employeeId = $_POST['editEmployeeId'];
            
                // Fetch employee information
                $sqlEmployee = "SELECT * FROM EMPLOYEE WHERE EMPLOYEEID = :eid";
                $stmtEmployee = oci_parse($conn_local, $sqlEmployee);
                oci_bind_by_name($stmtEmployee, ":eid", $employeeId);
                oci_execute($stmtEmployee);
                $rowEmployee = oci_fetch_array($stmtEmployee, OCI_ASSOC+OCI_RETURN_NULLS);
            
                // Fetch financial information
                $sqlFinancial = "SELECT * FROM FINANCIAL_INFO WHERE FINANCIALINFOID = :fid";
                $stmtFinancial = oci_parse($conn_local, $sqlFinancial);
                oci_bind_by_name($stmtFinancial, ":fid", $employeeId);
                oci_execute($stmtFinancial);
                $rowFinancial = oci_fetch_array($stmtFinancial, OCI_ASSOC+OCI_RETURN_NULLS);
            
                if ($rowEmployee && $rowFinancial) {
                    // Display the form with current information
                    echo '<form action="" method="post">';
                    echo '<h2>Update Employee Information</h2>';
            
                    // Display non-editable employee information
                    echo 'First Name: ' . htmlspecialchars($rowEmployee['FIRSTNAME']) . '<br>';
                    echo 'Last Name: ' . htmlspecialchars($rowEmployee['LASTNAME']) . '<br>';
            
                    // Editable fields
                    echo 'Total Hours: <input type="number" name="totalHours" value="' . htmlspecialchars($rowEmployee['TOTALHOURS']) . '" required><br>';
                    echo 'Commission Amount: <input type="text" name="commissionAmount" value="' . htmlspecialchars($rowEmployee['COMMISSIONAMOUNT']) . '" required><br>';
            
                    // Display non-editable financial information
                    echo 'Account Number: ' . htmlspecialchars($rowFinancial['ACCOUNTNUMBER']) . '<br>';
                    echo 'Institution Number: ' . htmlspecialchars($rowFinancial['INSTITUTIONNUMBER']) . '<br>';
                    echo 'Branch Number: ' . htmlspecialchars($rowFinancial['BRANCHNUMBER']) . '<br>';
            
                    // Editable financial field
                    echo 'Payment Count: <input type="number" name="paymentCount" value="' . htmlspecialchars($rowFinancial['PAYMENT_COUNT']) . '" required><br>';
            
                    // Hidden field to carry employee ID
                    echo '<input type="hidden" name="employeeId" value="' . htmlspecialchars($employeeId) . '">';
            
                    echo '<input type="submit" name="updateEmployee" value="Update Employee">';
                    echo '</form>';
                } else {
                    echo 'No employee found with ID ' . htmlspecialchars($employeeId);
                }
            }
          
            if (isset($_POST['updateEmployee'])) {
                $employeeId = $_POST['employeeId'];
                $totalHours = $_POST['totalHours'];
                $commissionAmount = $_POST['commissionAmount'];
                $paymentCount = $_POST['paymentCount'];
            
                // Update employee information
                $sqlUpdateEmployee = "UPDATE EMPLOYEE SET TOTALHOURS = :totalHours, COMMISSIONAMOUNT = :commissionAmount WHERE EMPLOYEEID = :eid";
                $stmtUpdateEmployee = oci_parse($conn_local, $sqlUpdateEmployee);
                oci_bind_by_name($stmtUpdateEmployee, ":totalHours", $totalHours);
                oci_bind_by_name($stmtUpdateEmployee, ":commissionAmount", $commissionAmount);
                oci_bind_by_name($stmtUpdateEmployee, ":eid", $employeeId);
                oci_execute($stmtUpdateEmployee);
            
                // Update financial information
                $sqlUpdateFinancial = "UPDATE FINANCIAL_INFO SET PAYMENT_COUNT = :paymentCount WHERE FINANCIALINFOID = :fid";
                $stmtUpdateFinancial = oci_parse($conn_local, $sqlUpdateFinancial);
                oci_bind_by_name($stmtUpdateFinancial, ":paymentCount", $paymentCount);
                oci_bind_by_name($stmtUpdateFinancial, ":fid", $employeeId);
                oci_execute($stmtUpdateFinancial);
            
                echo "Employee information updated successfully.";
            }
            
            
                oci_close($conn_local);
            }
    ?>
    
    </div>
    <div class="container">
        <form action="" method="post">
            <h2>Search for an Employee</h2>
            <input type="number" name="searchEmployeeId" placeholder="Enter Employee ID" required>
            <input type="submit" name="searchEmployee" value="Search Employee">
        </form>
        <h2>Add Employee with Financial Information</h2>
        <form action="" method="post">
            <!-- Employee Information -->
            <fieldset>
                <legend>Employee Information:</legend>
                <input type="text" name="firstName" placeholder="First Name" required>
                <input type="text" name="lastName" placeholder="Last Name" required>
                <input type="text" name="commissionAmount" placeholder="Commission Amount" required>
                <input type="text" name="totalHours" placeholder="Total Hours" required>
                <input type="date" name="startDate" placeholder="Start Date (YYYY-MM-DD)" required>
            </fieldset>

            <!-- Financial Information -->
            <fieldset>
                <legend>Financial Information:</legend>
                <input type="text" name="accountNumber" placeholder="Account Number" required>
                <input type="text" name="institutionNumber" placeholder="Institution Number" required>
                <input type="text" name="branchNumber" placeholder="Branch Number" required>
            </fieldset>

            
            <input type="submit" name="addEmployee" value="Add Employee">
        </form>
        <form action="" method="post">
            <h2>Delete an Employee</h2>
            <input type="number" name="deleteEmployeeId" placeholder="Enter Employee ID" required>
            <input type="submit" name="deleteEmployee" value="Delete Employee">
        </form>
       
        <form action="" method="post">
            <h2>Edit Employee Information</h2>
            <input type="number" name="editEmployeeId" placeholder="Enter Employee ID" required>
            <input type="submit" name="searchForEdit" value="Search Employee">
        </form>



    </div>
   
</body>
</html>