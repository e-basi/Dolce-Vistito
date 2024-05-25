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
    <form action="" method="post">
        <input type="submit" name="createTables" value="Create Tables"/>
        <input type="submit" name="displayAllTablesData" value="Display Data of All Tables"/>
        <input type="submit" name="deleteAllTables" value="Delete All Tables" onclick="return confirm('Are you sure you want to delete all tables? This cannot be undone!');"/>
        <input type="submit" name="populateTables" value="Populate Tables"/>
        <input type="submit" name="runQueries" value="Run Queries"/>
    </form>
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

        // Handle Display All Tables Data button
        if (isset($_POST['displayAllTablesData'])) {
            $query = "SELECT table_name FROM user_tables";
            $stid = oci_parse($conn_local, $query);
            $r = oci_execute($stid);

            if ($r) {
                while ($table = oci_fetch_array($stid, OCI_ASSOC)) {
                    $tableName = $table['TABLE_NAME'];

                    // Fetch data from each table
                    $dataQuery = "SELECT * FROM " . htmlspecialchars($tableName);
                    $dataStmt = oci_parse($conn_local, $dataQuery);
                    if (oci_execute($dataStmt)) {
                        echo "<h3>Table: " . htmlspecialchars($tableName) . "</h3>";
                        echo "<table border='1'>";

                        // Display headers
                        $ncols = oci_num_fields($dataStmt);
                        echo "<tr>";
                        for ($i = 1; $i <= $ncols; $i++) {
                            $column_name = oci_field_name($dataStmt, $i);
                            echo "<th>" . htmlspecialchars($column_name) . "</th>";
                        }
                        echo "</tr>";

                        // Display data
                        while ($row = oci_fetch_array($dataStmt, OCI_ASSOC+OCI_RETURN_NULLS)) {
                            echo "<tr>";
                            foreach ($row as $item) {
                                echo "<td>" . ($item !== null ? htmlspecialchars($item) : "&nbsp;") . "</td>";
                            }
                            echo "</tr>";
                        }
                        echo "</table><br>";
                    }
                }
            } else {
                $e = oci_error($stid);
                echo "Error retrieving table list: " . $e['message'];
            }
        }

        if (isset($_POST['createTables'])) {
            $createTableQueries = [
                "CREATE TABLE CATEGORY (
                    CATEGORYID NUMBER NOT NULL,
                    BOTTOMWEAR NUMBER(38),
                    TOPWEAR NUMBER(38),
                    PURSE NUMBER(38),
                    DRESS NUMBER(38),
                    ACCESSORIES NUMBER(38),
                    CONSTRAINT pk_CATEGORY PRIMARY KEY (CATEGORYID)
                )",
                "CREATE TABLE CUSTOMER (
                    CUSTOMERID NUMBER NOT NULL,
                    FIRSTNAME VARCHAR2(15),
                    LASTNAME VARCHAR2(15),
                    PHONE VARCHAR2(13),
                    EMAIL VARCHAR2(30),
                    TOTAL_PURCHASES NUMBER(10,2) DEFAULT 0,
                    CONSTRAINT pk_CUSTOMER PRIMARY KEY (CUSTOMERID)
                )",
                "CREATE TABLE PURCHASE_HISTORY (
                    PURCHASEID NUMBER NOT NULL,
                    CUSTOMERID NUMBER NOT NULL,
                    PURCHASEDATE DATE NOT NULL,
                    TOTALAMOUNT NUMBER(10,2),
                    CONSTRAINT pk_PURCHASE_HISTORY PRIMARY KEY (PURCHASEID),
                    CONSTRAINT fk_PURCHASE_HISTORY_CUSTOMER FOREIGN KEY (CUSTOMERID) REFERENCES CUSTOMER(CUSTOMERID)
                )",
                "CREATE TABLE PRODUCT (
                    PRODUCTID NUMBER NOT NULL,
                    PRODUCTSIZE VARCHAR2(10),
                    PRICE NUMBER(10,2),
                    COLOR VARCHAR2(20),
                    PRODUCTNAME VARCHAR2(50) NOT NULL,
                    CONSTRAINT pk_PRODUCT PRIMARY KEY (PRODUCTID)
                )",
                "CREATE TABLE ProductCategory (
                    PRODUCTID NUMBER NOT NULL,
                    CATEGORYID NUMBER NOT NULL,
                    CONSTRAINT pk_ProductCategory PRIMARY KEY (PRODUCTID, CATEGORYID),
                    CONSTRAINT fk_ProductCategory_Product FOREIGN KEY (PRODUCTID) REFERENCES PRODUCT(PRODUCTID),
                    CONSTRAINT fk_ProductCategory_Category FOREIGN KEY (CATEGORYID) REFERENCES CATEGORY(CATEGORYID)
                )",
                "CREATE TABLE INVENTORY (
                    INVENTORYID NUMBER NOT NULL,
                    PRODUCTID NUMBER NOT NULL,
                    QUANTITY NUMBER DEFAULT 0,
                    CONSTRAINT pk_INVENTORY PRIMARY KEY (INVENTORYID),
                    CONSTRAINT fk_INVENTORY_PRODUCT FOREIGN KEY (PRODUCTID) REFERENCES PRODUCT(PRODUCTID)
                )",
                "CREATE TABLE FINANCIAL_INFO (
                    FINANCIALINFOID NUMBER NOT NULL,
                    ACCOUNTNUMBER VARCHAR2(20) NOT NULL,
                    INSTITUTIONNUMBER VARCHAR2(10) NOT NULL,
                    BRANCHNUMBER VARCHAR2(10) NOT NULL,
                    PAYMENT_COUNT NUMBER DEFAULT 0,
                    CONSTRAINT pk_FINANCIAL_INFO PRIMARY KEY (FINANCIALINFOID)
                )",
                "CREATE TABLE EMPLOYEE (
                    EMPLOYEEID NUMBER NOT NULL,
                    COMMISSIONAMOUNT DECIMAL(10,2),
                    TOTALHOURS DECIMAL(10,2),
                    FIRSTNAME VARCHAR(255),
                    LASTNAME VARCHAR(255),
                    TOTALSALE DECIMAL(10,2),
                    FINANCIALINFOID INT,
                    STARTDATE DATE,
                    CONSTRAINT pk_EMPLOYEE PRIMARY KEY (EMPLOYEEID),
                    CONSTRAINT fk_EMPLOYEE_FinancialInfo FOREIGN KEY (FINANCIALINFOID) REFERENCES FINANCIAL_INFO(FINANCIALINFOID)
                )",
                "CREATE TABLE TRANSACTION (
                    TRANSACTIONID NUMBER NOT NULL,
                    TRANSACTIONDATE DATE NOT NULL,
                    TOTALAMOUNT NUMBER(10,2) NOT NULL,
                    ISRETURNED NUMBER(1) DEFAULT 0,
                    CUSTOMERID NUMBER NOT NULL,
                    EMPLOYEEID NUMBER NOT NULL,
                    CONSTRAINT pk_TRANSACTION PRIMARY KEY (TRANSACTIONID),
                    CONSTRAINT fk_TRANSACTION_CUSTOMER FOREIGN KEY (CUSTOMERID) REFERENCES CUSTOMER(CUSTOMERID),
                    CONSTRAINT fk_TRANSACTION_EMPLOYEE FOREIGN KEY (EMPLOYEEID) REFERENCES EMPLOYEE(EMPLOYEEID)
                )",
                "CREATE TABLE TRANSACTION_LINE (
                    TRANSACTIONLINEID NUMBER NOT NULL,
                    TRANSACTIONID NUMBER NOT NULL,
                    PRODUCTID NUMBER NOT NULL,
                    QUANTITY NUMBER NOT NULL,
                    CONSTRAINT pk_TRANSACTION_LINE PRIMARY KEY (TRANSACTIONLINEID),
                    CONSTRAINT fk_TL_TRANS FOREIGN KEY (TRANSACTIONID) REFERENCES TRANSACTION(TRANSACTIONID),
                    CONSTRAINT fk_TL_PROD FOREIGN KEY (PRODUCTID) REFERENCES PRODUCT(PRODUCTID)
                )",
                "CREATE TABLE RETURN_HISTORY (
                    RETURNID NUMBER NOT NULL,
                    TRANSACTIONID NUMBER NOT NULL,
                    RETURNDATE DATE NOT NULL,
                    REFUNDEDAMOUNT NUMBER(10,2) NOT NULL,
                    CONSTRAINT pk_RETURN_HISTORY PRIMARY KEY (RETURNID),
                    CONSTRAINT fk_RH_TRANS FOREIGN KEY (TRANSACTIONID) REFERENCES TRANSACTION(TRANSACTIONID)
                )"
                // ... Add other tables if needed ...
            ];
        
            foreach ($createTableQueries as $query) {
                $stid = oci_parse($conn_local, $query);
                $r = oci_execute($stid);
                if (!$r) {
                    $e = oci_error($stid);
                    echo "Error creating table: " . $e['message'] . "<br/>";
                    break;
                }
            }
        
            
        // SQL to create AFTER_TRANSACTION trigger
        $afterTransactionTriggerSQL = "CREATE OR REPLACE TRIGGER AFTER_TRANSACTION
        AFTER INSERT ON TRANSACTION
        FOR EACH ROW
        BEGIN
            -- Update the employee's total sales and commission amount
            -- Commission is calculated on the amount excluding tax
            UPDATE EMPLOYEE
            SET TOTALSALE = TOTALSALE + (:NEW.TOTALAMOUNT / 1.13),
                COMMISSIONAMOUNT = COMMISSIONAMOUNT + ((:NEW.TOTALAMOUNT / 1.13) * 0.015)
            WHERE EMPLOYEEID = :NEW.EMPLOYEEID;

            -- Update the customer's total purchases
            UPDATE CUSTOMER
            SET TOTAL_PURCHASES = TOTAL_PURCHASES + :NEW.TOTALAMOUNT
            WHERE CUSTOMERID = :NEW.CUSTOMERID;

            -- Insert into PURCHASE_HISTORY
            -- Note: This assumes each transaction will have only one entry in PURCHASE_HISTORY
            INSERT INTO PURCHASE_HISTORY (PURCHASEID, CUSTOMERID, PURCHASEDATE, TOTALAMOUNT)
            VALUES (:NEW.TRANSACTIONID, :NEW.CUSTOMERID, SYSDATE, :NEW.TOTALAMOUNT);

        EXCEPTION
            WHEN OTHERS THEN
                -- Error handling logic here
                RAISE;
        END;";  // Complete trigger definition here
            $stmt = oci_parse($conn_local, $afterTransactionTriggerSQL);
            oci_execute($stmt);

            // SQL to create AFTER_TRANSACTION_LINE trigger
            $afterTransactionLineTriggerSQL = "CREATE OR REPLACE TRIGGER AFTER_TRANSACTION_LINE
        AFTER INSERT ON TRANSACTION_LINE
        FOR EACH ROW
        DECLARE
            v_product_price NUMBER;
            v_line_total_ex_tax NUMBER;
            v_line_tax NUMBER;
            v_total_amount NUMBER;
        BEGIN
            -- Retrieve product price
            SELECT PRICE INTO v_product_price FROM PRODUCT WHERE PRODUCTID = :NEW.PRODUCTID;

            -- Calculate line total excluding tax
            v_line_total_ex_tax := v_product_price * :NEW.QUANTITY;

            -- Calculate line tax
            v_line_tax := v_line_total_ex_tax * 0.13;  -- Assuming 13% tax rate

            -- Update inventory
            UPDATE INVENTORY
            SET QUANTITY = QUANTITY - :NEW.QUANTITY
            WHERE PRODUCTID = :NEW.PRODUCTID;

            -- Update the TRANSACTION total amount with the line total including tax
            UPDATE TRANSACTION
            SET TOTALAMOUNT = TOTALAMOUNT + v_line_total_ex_tax + v_line_tax
            WHERE TRANSACTIONID = :NEW.TRANSACTIONID;

            -- Update employee total sales and commission
            UPDATE EMPLOYEE
            SET TOTALSALE = TOTALSALE + v_line_total_ex_tax,
                COMMISSIONAMOUNT = COMMISSIONAMOUNT + (v_line_total_ex_tax * 0.015)
            WHERE EMPLOYEEID = (SELECT EMPLOYEEID FROM TRANSACTION WHERE TRANSACTIONID = :NEW.TRANSACTIONID);

            -- Update customer total purchases
            UPDATE CUSTOMER
            SET TOTAL_PURCHASES = TOTAL_PURCHASES + (v_line_total_ex_tax + v_line_tax)
            WHERE CUSTOMERID = (SELECT CUSTOMERID FROM TRANSACTION WHERE TRANSACTIONID = :NEW.TRANSACTIONID);

            -- Calculate the total amount for the purchase history
            v_total_amount := v_line_total_ex_tax + v_line_tax;

            -- Insert or update PURCHASE_HISTORY record
            MERGE INTO PURCHASE_HISTORY ph
            USING (SELECT :NEW.TRANSACTIONID AS TRANSACTIONID FROM dual) t
            ON (ph.PURCHASEID = t.TRANSACTIONID)
            WHEN MATCHED THEN
                UPDATE SET ph.TOTALAMOUNT = ph.TOTALAMOUNT + v_total_amount
            WHEN NOT MATCHED THEN
                INSERT (PURCHASEID, CUSTOMERID, PURCHASEDATE, TOTALAMOUNT)
                VALUES (:NEW.TRANSACTIONID, (SELECT CUSTOMERID FROM TRANSACTION WHERE TRANSACTIONID = :NEW.TRANSACTIONID), SYSDATE, v_total_amount);

        EXCEPTION
            WHEN OTHERS THEN
                -- Consider logging the error or re-raise with additional info
                RAISE;
        END;";  // Complete trigger definition here
        $stmt = oci_parse($conn_local, $afterTransactionLineTriggerSQL);
        oci_execute($stmt);
    echo "All tables created successfully.";
        }
        
        

        if (isset($_POST['deleteAllTables'])) {
            // Get list of all table names
            $query = "SELECT table_name FROM user_tables";
            $stid = oci_parse($conn_local, $query);
            oci_execute($stid);
        
            while ($row = oci_fetch_array($stid, OCI_ASSOC)) {
                $tableName = $row['TABLE_NAME'];
        
                // Generate and execute DROP TABLE statement for each table
                $dropQuery = "DROP TABLE " . htmlspecialchars($tableName) . " CASCADE CONSTRAINTS";
                $dropStmt = oci_parse($conn_local, $dropQuery);
                oci_execute($dropStmt);
            }
        
            echo "All tables have been deleted.";
        }

        if (isset($_POST['populateTables'])) {
            $insertQueries = [
                //product
                "INSERT INTO PRODUCT (PRODUCTID, PRODUCTSIZE, PRICE, COLOR, PRODUCTNAME) VALUES (101, 'M', 19.99, 'White', 'T-shirt')",
                "INSERT INTO PRODUCT (PRODUCTID, PRODUCTSIZE, PRICE, COLOR, PRODUCTNAME) VALUES (102, 'L', 24.99, 'Green', 'Blouse')",
                "INSERT INTO PRODUCT (PRODUCTID, PRODUCTSIZE, PRICE, COLOR, PRODUCTNAME) VALUES (103, 'XL', 29.99, 'Black', 'Jacket')",
                "INSERT INTO PRODUCT (PRODUCTID, PRODUCTSIZE, PRICE, COLOR, PRODUCTNAME) VALUES (201, 'L', 39.99, 'Blue', 'Jeans')",
                "INSERT INTO PRODUCT (PRODUCTID, PRODUCTSIZE, PRICE, COLOR, PRODUCTNAME) VALUES (202, 'M', 25.99, 'Red', 'Shorts')",
                "INSERT INTO PRODUCT (PRODUCTID, PRODUCTSIZE, PRICE, COLOR, PRODUCTNAME) VALUES (203, 'S', 30.99, 'Black', 'Skirts')",
                "INSERT INTO PRODUCT (PRODUCTID, PRODUCTSIZE, PRICE, COLOR, PRODUCTNAME) VALUES (301, 'One Size', 49.99, 'Black', 'Handbag')",
                "INSERT INTO PRODUCT (PRODUCTID, PRODUCTSIZE, PRICE, COLOR, PRODUCTNAME) VALUES (302, 'One Size', 45.99, 'Red', 'Clutch')",
                "INSERT INTO PRODUCT (PRODUCTID, PRODUCTSIZE, PRICE, COLOR, PRODUCTNAME) VALUES (303, 'One Size', 55.99, 'Brown', 'Satchel')",
                "INSERT INTO PRODUCT (PRODUCTID, PRODUCTSIZE, PRICE, COLOR, PRODUCTNAME) VALUES (401, 'M', 60.99, 'Navy', 'Evening Dress')",
                "INSERT INTO PRODUCT (PRODUCTID, PRODUCTSIZE, PRICE, COLOR, PRODUCTNAME) VALUES (402, 'S', 35.99, 'Yellow', 'Sundress')",
                "INSERT INTO PRODUCT (PRODUCTID, PRODUCTSIZE, PRICE, COLOR, PRODUCTNAME) VALUES (403, 'L', 45.99, 'Red', 'Cocktail Dress')",
                "INSERT INTO PRODUCT (PRODUCTID, PRODUCTSIZE, PRICE, COLOR, PRODUCTNAME) VALUES (501, 'One Size', 15.99, 'Black', 'Hat')",
                "INSERT INTO PRODUCT (PRODUCTID, PRODUCTSIZE, PRICE, COLOR, PRODUCTNAME) VALUES (502, 'One Size', 25.99, 'Brown', 'Sunglasses')",
                "INSERT INTO PRODUCT (PRODUCTID, PRODUCTSIZE, PRICE, COLOR, PRODUCTNAME) VALUES (503, 'One Size', 12.99, 'Red', 'Scarf')",
                //Category
                "INSERT INTO CATEGORY (CATEGORYID, BOTTOMWEAR, TOPWEAR, PURSE, DRESS, ACCESSORIES) VALUES (101, 0, 1, 0, 0, 0)",
                "INSERT INTO CATEGORY (CATEGORYID, BOTTOMWEAR, TOPWEAR, PURSE, DRESS, ACCESSORIES) VALUES (102, 0, 1, 0, 0, 0)",
                "INSERT INTO CATEGORY (CATEGORYID, BOTTOMWEAR, TOPWEAR, PURSE, DRESS, ACCESSORIES) VALUES (103, 0, 1, 0, 0, 0)",
                "INSERT INTO CATEGORY (CATEGORYID, BOTTOMWEAR, TOPWEAR, PURSE, DRESS, ACCESSORIES) VALUES (201, 1, 0, 0, 0, 0)",
                "INSERT INTO CATEGORY (CATEGORYID, BOTTOMWEAR, TOPWEAR, PURSE, DRESS, ACCESSORIES) VALUES (202, 1, 0, 0, 0, 0)",
                "INSERT INTO CATEGORY (CATEGORYID, BOTTOMWEAR, TOPWEAR, PURSE, DRESS, ACCESSORIES) VALUES (203, 1, 0, 0, 0, 0)",
                "INSERT INTO CATEGORY (CATEGORYID, BOTTOMWEAR, TOPWEAR, PURSE, DRESS, ACCESSORIES) VALUES (301, 0, 0, 1, 0, 0)",
                "INSERT INTO CATEGORY (CATEGORYID, BOTTOMWEAR, TOPWEAR, PURSE, DRESS, ACCESSORIES) VALUES (302, 0, 0, 1, 0, 0)",
                "INSERT INTO CATEGORY (CATEGORYID, BOTTOMWEAR, TOPWEAR, PURSE, DRESS, ACCESSORIES) VALUES (303, 0, 0, 1, 0, 0)",
                "INSERT INTO CATEGORY (CATEGORYID, BOTTOMWEAR, TOPWEAR, PURSE, DRESS, ACCESSORIES) VALUES (401, 0, 0, 0, 1, 0)",
                "INSERT INTO CATEGORY (CATEGORYID, BOTTOMWEAR, TOPWEAR, PURSE, DRESS, ACCESSORIES) VALUES (402, 0, 0, 0, 1, 0)",
                "INSERT INTO CATEGORY (CATEGORYID, BOTTOMWEAR, TOPWEAR, PURSE, DRESS, ACCESSORIES) VALUES (403, 0, 0, 0, 1, 0)",
                "INSERT INTO CATEGORY (CATEGORYID, BOTTOMWEAR, TOPWEAR, PURSE, DRESS, ACCESSORIES) VALUES (501, 0, 0, 0, 0, 1)",
                "INSERT INTO CATEGORY (CATEGORYID, BOTTOMWEAR, TOPWEAR, PURSE, DRESS, ACCESSORIES) VALUES (502, 0, 0, 0, 0, 1)",
                "INSERT INTO CATEGORY (CATEGORYID, BOTTOMWEAR, TOPWEAR, PURSE, DRESS, ACCESSORIES) VALUES (503, 0, 0, 0, 0, 1)",
                //product category
                "INSERT INTO PRODUCTCATEGORY (PRODUCTID, CATEGORYID) VALUES (101, 101)",
                "INSERT INTO PRODUCTCATEGORY (PRODUCTID, CATEGORYID) VALUES (102, 102)",
                "INSERT INTO PRODUCTCATEGORY (PRODUCTID, CATEGORYID) VALUES (103, 103)",
                "INSERT INTO PRODUCTCATEGORY (PRODUCTID, CATEGORYID) VALUES (201, 201)",
                "INSERT INTO PRODUCTCATEGORY (PRODUCTID, CATEGORYID) VALUES (202, 202)",
                "INSERT INTO PRODUCTCATEGORY (PRODUCTID, CATEGORYID) VALUES (203, 203)",
                "INSERT INTO PRODUCTCATEGORY (PRODUCTID, CATEGORYID) VALUES (301, 301)",
                "INSERT INTO PRODUCTCATEGORY (PRODUCTID, CATEGORYID) VALUES (302, 302)",
                "INSERT INTO PRODUCTCATEGORY (PRODUCTID, CATEGORYID) VALUES (303, 303)",
                "INSERT INTO PRODUCTCATEGORY (PRODUCTID, CATEGORYID) VALUES (401, 401)",
                "INSERT INTO PRODUCTCATEGORY (PRODUCTID, CATEGORYID) VALUES (402, 402)",
                "INSERT INTO PRODUCTCATEGORY (PRODUCTID, CATEGORYID) VALUES (403, 403)",
                "INSERT INTO PRODUCTCATEGORY (PRODUCTID, CATEGORYID) VALUES (501, 501)",
                "INSERT INTO PRODUCTCATEGORY (PRODUCTID, CATEGORYID) VALUES (502, 502)",
                "INSERT INTO PRODUCTCATEGORY (PRODUCTID, CATEGORYID) VALUES (503, 503)",

                //Inventory
                "INSERT INTO INVENTORY (INVENTORYID, PRODUCTID, QUANTITY) VALUES (1, 101, 20)",
                "INSERT INTO INVENTORY (INVENTORYID, PRODUCTID, QUANTITY) VALUES (2, 102, 15)",
                "INSERT INTO INVENTORY (INVENTORYID, PRODUCTID, QUANTITY) VALUES (3, 103, 13)",
                "INSERT INTO INVENTORY (INVENTORYID, PRODUCTID, QUANTITY) VALUES (4, 201, 10)",
                "INSERT INTO INVENTORY (INVENTORYID, PRODUCTID, QUANTITY) VALUES (5, 202, 11)",
                "INSERT INTO INVENTORY (INVENTORYID, PRODUCTID, QUANTITY) VALUES (6, 203, 19)",
                "INSERT INTO INVENTORY (INVENTORYID, PRODUCTID, QUANTITY) VALUES (7, 301, 18)",
                "INSERT INTO INVENTORY (INVENTORYID, PRODUCTID, QUANTITY) VALUES (8, 302, 10)",
                "INSERT INTO INVENTORY (INVENTORYID, PRODUCTID, QUANTITY) VALUES (9, 303, 12)",
                "INSERT INTO INVENTORY (INVENTORYID, PRODUCTID, QUANTITY) VALUES (10, 401, 11)",
                "INSERT INTO INVENTORY (INVENTORYID, PRODUCTID, QUANTITY) VALUES (11, 402, 19)",
                "INSERT INTO INVENTORY (INVENTORYID, PRODUCTID, QUANTITY) VALUES (12, 403, 25)",
                "INSERT INTO INVENTORY (INVENTORYID, PRODUCTID, QUANTITY) VALUES (13, 501, 23)",
                "INSERT INTO INVENTORY (INVENTORYID, PRODUCTID, QUANTITY) VALUES (14, 502, 16)",
                "INSERT INTO INVENTORY (INVENTORYID, PRODUCTID, QUANTITY) VALUES (15, 503, 17)",
                
                //finacial info
                "INSERT INTO FINANCIAL_INFO (FINANCIALINFOID, ACCOUNTNUMBER, INSTITUTIONNUMBER, BRANCHNUMBER) VALUES (18001, '1234567890', '001', '0001')",
                "INSERT INTO FINANCIAL_INFO (FINANCIALINFOID, ACCOUNTNUMBER, INSTITUTIONNUMBER, BRANCHNUMBER) VALUES (18002, '1234567891', '002', '0002')",
                "INSERT INTO FINANCIAL_INFO (FINANCIALINFOID, ACCOUNTNUMBER, INSTITUTIONNUMBER, BRANCHNUMBER) VALUES (18003, '1234567892', '003', '0003')",
                "INSERT INTO FINANCIAL_INFO (FINANCIALINFOID, ACCOUNTNUMBER, INSTITUTIONNUMBER, BRANCHNUMBER) VALUES (18004, '1234567893', '004', '0004')",
        
                //employee
                "INSERT INTO EMPLOYEE (EMPLOYEEID, COMMISSIONAMOUNT, TOTALHOURS, FIRSTNAME, LASTNAME, TOTALSALE, FINANCIALINFOID, STARTDATE) VALUES (18001, 1.77, 20, 'John', 'Doe', 117.97, 18001, TO_DATE('2001-01-23', 'YYYY-MM-DD'))",
                "INSERT INTO EMPLOYEE (EMPLOYEEID, COMMISSIONAMOUNT, TOTALHOURS, FIRSTNAME, LASTNAME, TOTALSALE, FINANCIALINFOID, STARTDATE) VALUES (18002, 0.9, 18, 'Jane', 'Smith', 59.97, 18002, TO_DATE('2015-01-23', 'YYYY-MM-DD'))",
                "INSERT INTO EMPLOYEE (EMPLOYEEID, COMMISSIONAMOUNT, TOTALHOURS, FIRSTNAME, LASTNAME, TOTALSALE, FINANCIALINFOID, STARTDATE) VALUES (18003, 1.38, 4, 'Alice', 'Johnson', 91.98, 18003, TO_DATE('2001-02-23', 'YYYY-MM-DD'))",
                "INSERT INTO EMPLOYEE (EMPLOYEEID, COMMISSIONAMOUNT, TOTALHOURS, FIRSTNAME, LASTNAME, TOTALSALE, FINANCIALINFOID, STARTDATE) VALUES (18004, 1.93, 25, 'Bob', 'Brown', 128.95, 18004, TO_DATE('2015-02-23', 'YYYY-MM-DD'))",            
                
                //customer
                "INSERT INTO CUSTOMER (CUSTOMERID, FIRSTNAME, LASTNAME, PHONE, EMAIL, TOTAL_PURCHASES) VALUES (1001, 'Emily', 'Johnson', '437-456-7000', 'emilson@gmail.com', 145.71)",
                "INSERT INTO CUSTOMER (CUSTOMERID, FIRSTNAME, LASTNAME, PHONE, EMAIL, TOTAL_PURCHASES) VALUES (1002, 'Michael', 'Brown', '647-456-7897', 'michael.brown@gmail.com', 133.31)",
                "INSERT INTO CUSTOMER (CUSTOMERID, FIRSTNAME, LASTNAME, PHONE, EMAIL, TOTAL_PURCHASES) VALUES (1003, 'Sarah', 'Davis', '416-456-9092', 'sarah.davis@gmail.com', 103.94)",
                "INSERT INTO CUSTOMER (CUSTOMERID, FIRSTNAME, LASTNAME, PHONE, EMAIL, TOTAL_PURCHASES) VALUES (1004, 'James', 'Wilson', '413-253-7233', 'jwilson@gmail.com', 0)",
                "INSERT INTO CUSTOMER (CUSTOMERID, FIRSTNAME, LASTNAME, PHONE, EMAIL, TOTAL_PURCHASES) VALUES (1005, 'Linda', 'Martinez', '416-666-8894', 'linda.martinez@gmail.com', 67.77)",

                //Transaction
                "INSERT INTO TRANSACTION (TRANSACTIONID, CUSTOMERID, EMPLOYEEID, TRANSACTIONDATE, TOTALAMOUNT) VALUES (1000, 1002, 18001, SYSDATE, 0)",
                "INSERT INTO TRANSACTION (TRANSACTIONID, CUSTOMERID, EMPLOYEEID, TRANSACTIONDATE, TOTALAMOUNT) VALUES (1001, 1003, 18003, SYSDATE, 0)",
                "INSERT INTO TRANSACTION (TRANSACTIONID, CUSTOMERID, EMPLOYEEID, TRANSACTIONDATE, TOTALAMOUNT) VALUES (1002, 1005, 18002, SYSDATE, 0)",
                "INSERT INTO TRANSACTION (TRANSACTIONID, CUSTOMERID, EMPLOYEEID, TRANSACTIONDATE, TOTALAMOUNT) VALUES (1003, 1001, 18004, SYSDATE, 0)",

                //Transaction_line
                "INSERT INTO TRANSACTION_LINE (TRANSACTIONLINEID, TRANSACTIONID, PRODUCTID, QUANTITY) VALUES (1, 1000, 402, 2)",
                "INSERT INTO TRANSACTION_LINE (TRANSACTIONLINEID, TRANSACTIONID, PRODUCTID, QUANTITY) VALUES (2, 1000, 302, 1)",
                "INSERT INTO TRANSACTION_LINE (TRANSACTIONLINEID, TRANSACTIONID, PRODUCTID, QUANTITY) VALUES (3, 1001, 403, 2)",
                "INSERT INTO TRANSACTION_LINE (TRANSACTIONLINEID, TRANSACTIONID, PRODUCTID, QUANTITY) VALUES (4, 1002, 101, 3)",
                "INSERT INTO TRANSACTION_LINE (TRANSACTIONLINEID, TRANSACTIONID, PRODUCTID, QUANTITY) VALUES (5, 1003, 502, 4)",
                "INSERT INTO TRANSACTION_LINE (TRANSACTIONLINEID, TRANSACTIONID, PRODUCTID, QUANTITY) VALUES (6, 1003, 102, 1)",

            ]; 
        
        
            foreach ($insertQueries as $query) {
                $stid = oci_parse($conn_local, $query);
                $r = oci_execute($stid);
                if (!$r) {
                    $e = oci_error($stid);
                    echo "Error populating table: " . $e['message'] . "<br/>";
                    break;
                }
            }
        
            echo "All tables populated successfully.";
        }
        if (isset($_POST['runQueries'])) {
            $queries = [
                [
                    "header" => "Total Amount Each Customer Has Purchased",
                    "query" => "SELECT C.CustomerID, C.FirstName, C.LastName, C.Phone, C.Email, SUM(PH.TotalAmount) AS TotalPurchasedAmount FROM CUSTOMER C INNER JOIN PURCHASE_HISTORY PH ON C.CustomerID = PH.CustomerID GROUP BY C.CustomerID, C.FirstName, C.LastName, C.Phone, C.Email ORDER BY C.CustomerID"
                ],
                [
                    "header" => "All Products in the Category Top Wear",
                    "query" => "SELECT p.PRODUCTID, p.PRODUCTNAME, p.PRODUCTSIZE, p.PRICE, p.COLOR 
                    FROM PRODUCT p
                    JOIN PRODUCTCATEGORY pc ON p.PRODUCTID = pc.PRODUCTID
                    JOIN CATEGORY c ON pc.CATEGORYID = c.CATEGORYID
                    WHERE c.TOPWEAR = 1"

                ],
                [
                    "header" => "All Employees Ordered by Total Sale Descending",
                    "query" => "SELECT e.TotalSale, e.CommissionAmount, e.FirstName, e.LastName, e.EmployeeID, e.FinancialInfoID FROM EMPLOYEE e ORDER BY TotalSale DESC"
                ],
                [
                    "header" => "Join tables FINANCIAL_INFO and EMPLOYEE to list all the employees names with their financial information",
                    "query" => "SELECT e.EmployeeID, e.FirstName, e.LastName, f.AccountNumber, f.InstitutionNumber, f.BranchNumber
                    FROM EMPLOYEE e
                    JOIN FINANCIAL_INFO f ON e.FinancialInfoID = f.FinancialInfoID"
                    
                ],
                [
                    "header" => "Shows the purchase history of customer 1001",
                    "query" => "SELECT PH. PurchaseID, PH.PurchaseDate, PH.TotalAmount
                    FROM  PURCHASE_HISTORY PH
                    WHERE PH.CustomerID = 1001"
                ],
                [
                    "header" => "List all total refund amount for all employees",
                    "query" => "SELECT
                    E.EmployeeID,
                    E.FirstName,
                    E.LastName,
                    SUM(RH.RefundedAmount) AS TotalRefunds
                    FROM EMPLOYEE E
                    LEFT JOIN TRANSACTION T ON E.EmployeeID = T.EmployeeID
                    LEFT JOIN RETURN_HISTORY RH ON T.TransactionID = RH.TransactionID
                    GROUP BY E.EmployeeID, E.FirstName, E.LastName
                    "
                ],
                [
                    "header" => "Lists all Products that have 0 in Stock",
                    "query" => "SELECT P.ProductID, P.ProductName, P.ProductSize
                    FROM PRODUCT P
                    WHERE P.ProductID NOT IN (
                        SELECT I.ProductID
                        FROM INVENTORY I
                        WHERE I.Quantity > 0
                    )"
                ],
                [
                    "header" => "List all customers who have bought items but haven't returned any of them",
                    "query" => "SELECT DISTINCT c.CUSTOMERID, C.FIRSTNAME, c. LASTNAME
                    FROM CUSTOMER c
                    JOIN PURCHASE_HISTORY ph ON c.CUSTOMERID = ph.CUSTOMERID
                    JOIN TRANSACTION t ON ph.PURCHASEID = t. TRANSACTIONID             
                    LEFT JOIN RETURN_HISTORY rh ON t. TRANSACTIONID = rh. TRANSACTIONID
                    WHERE rh. TRANSACTIONID IS NULL"
                ],
                [
                    "header" => "Lists the total amount each customer has purchased in their history shopping in dolce vistito",
                    "query" => "SELECT
                    C.CustomerID, C.FirstName,
                    C. LastName, C.Phone, C.Email,
                    SUM(PH.TotalAmount) AS TotalPurchasedAmount
                    FROM
                    CUSTOMER C
                    INNER JOIN
                    PURCHASE_HISTORY PH
                    ON
                    C.CustomerID = PH.CustomerID
                    GROUP BY
                    C.CustomerID, C.FirstName, C.LastName, C.Phone, C.Email
                    ORDER BY C.CustomerID"
                ],
                [
                    "header" => "List all employees by descending order by their TotalSale",
                    "query" => "SELECT e.TotalSale, e.CommissionAmount, e.FirstName, e.LastName, e.EmployeeID, e.FinancialInfoID
                    FROM EMPLOYEE e
                    ORDER BY TotalSale DESC"
            
                ],
                [
                    "header" => "list all the employees names with their financial information",
                    "query" => "SELECT e.EmployeeID, e.FirstName, e.LastName, f.AccountNumber, f.InstitutionNumber, f.BranchNumber
                    FROM EMPLOYEE e
                    JOIN FINANCIAL_INFO f ON e.FinancialInfoID = f.FinancialInfoID"
                ],
                [
                    "header" => "Number of Transactions Handled by Each Employee",
                    "query" => "SELECT E.FIRSTNAME, E.LASTNAME, COUNT(T.TRANSACTIONID) AS NumberOfTransactionsHandled
                    FROM EMPLOYEE E
                    LEFT JOIN TRANSACTION T ON E.EMPLOYEEID = T.EMPLOYEEID
                    GROUP BY E.FIRSTNAME, E.LASTNAME
                    HAVING COUNT(T.TRANSACTIONID) > 0"
                ],
                [
                    "header" => "Average Price of Products in Each Category with Price Above $20",
                    "query" => "SELECT 
                    CASE 
                    WHEN C.BOTTOMWEAR = 1 THEN 'Bottom Wear'
                    WHEN C.TOPWEAR = 1 THEN 'Top Wear'
                    WHEN C.PURSE = 1 THEN 'Purse'
                    WHEN C.DRESS = 1 THEN 'Dress'
                    ELSE 'Accessories'
                    END AS Category,
                    AVG(P.PRICE) AS AveragePrice
                FROM PRODUCT P
                JOIN ProductCategory PC ON P.PRODUCTID = PC.PRODUCTID
                JOIN CATEGORY C ON PC.CATEGORYID = C.CATEGORYID
                GROUP BY 
                    CASE 
                    WHEN C.BOTTOMWEAR = 1 THEN 'Bottom Wear'
                    WHEN C.TOPWEAR = 1 THEN 'Top Wear'
                    WHEN C.PURSE = 1 THEN 'Purse'
                    WHEN C.DRESS = 1 THEN 'Dress'
                    ELSE 'Accessories'
                    END
                HAVING AVG(P.PRICE) > 20"
                ],
                
                [
                    "header" => "List of Products That Are Either 'Bottom Wear' or 'Top Wear'",
                    "query" => "SELECT P.PRODUCTNAME
                    FROM PRODUCT P
                    JOIN ProductCategory PC ON P.PRODUCTID = PC.PRODUCTID
                    JOIN CATEGORY C ON PC.CATEGORYID = C.CATEGORYID
                    WHERE C.BOTTOMWEAR = 1
                    UNION
                    SELECT P.PRODUCTNAME
                    FROM PRODUCT P
                    JOIN ProductCategory PC ON P.PRODUCTID = PC.PRODUCTID
                    JOIN CATEGORY C ON PC.CATEGORYID = C.CATEGORYID
                    WHERE C.TOPWEAR = 1"
                ],
                [
                    "header" => "Count of Products in Each Category",
                    "query" => "SELECT 
                    CASE 
                    WHEN C.BOTTOMWEAR = 1 THEN 'Bottom Wear'
                    WHEN C.TOPWEAR = 1 THEN 'Top Wear'
                    WHEN C.PURSE = 1 THEN 'Purse'
                    WHEN C.DRESS = 1 THEN 'Dress'
                    ELSE 'Accessories'
                    END AS Category,
                    COUNT(PC.PRODUCTID) AS ProductCount
                FROM CATEGORY C
                JOIN ProductCategory PC ON C.CATEGORYID = PC.CATEGORYID
                GROUP BY 
                    C.BOTTOMWEAR, C.TOPWEAR, C.PURSE, C.DRESS, C.ACCESSORIES"
                ]
                


            ];
        
            foreach ($queries as $item) {
                $query = $item["query"];
                $header = $item["header"];
                $stid = oci_parse($conn_local, $query);
                oci_execute($stid);
    
                echo "<div class='table-wrapper'>";
                echo "<h3>" . htmlspecialchars($header) . "</h3>";
                echo "<table border='1'>";
                // Display headers
                $ncols = oci_num_fields($stid);
                echo "<tr>";
                for ($i = 1; $i <= $ncols; $i++) {
                    $column_name = oci_field_name($stid, $i);
                    echo "<th>" . htmlspecialchars($column_name) . "</th>";
                }
                echo "</tr>";
    
                // Display data
                while ($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS)) {
                    echo "<tr>";
                    foreach ($row as $item) {
                        echo "<td>" . ($item !== null ? htmlspecialchars($item) : "&nbsp;") . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
                echo "</div>";
            }
        }
        
    
        // Close the local database connection
        oci_close($conn_local);
    }
    ?>
</div>

</body>
</html>
