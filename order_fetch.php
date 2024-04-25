<?php
//order_fetch.php

include('database_connection.php');
include('function.php');

// Initialize variables
$query = '';
$output = array();
$data = array();

// Build the SQL query
$query .= "SELECT * FROM inventory_order WHERE ";

// Check user type and add condition accordingly
if ($_SESSION['type'] == 'user') {
    $query .= 'user_id = :user_id AND ';
}

// Add search condition if search value is provided
if (isset($_POST["search"]["value"])) {
    $search_value = '%' . $_POST["search"]["value"] . '%';
    $query .= '(inventory_order_id LIKE :search_value ';
    $query .= 'OR inventory_order_name LIKE :search_value ';
    $query .= 'OR inventory_order_total LIKE :search_value ';
    $query .= 'OR inventory_order_status LIKE :search_value ';
    $query .= 'OR inventory_order_date LIKE :search_value) ';
}

// Add order condition if specified
if (isset($_POST["order"])) {
    $query .= 'ORDER BY ' . $_POST['order']['0']['column'] . ' ' . $_POST['order']['0']['dir'] . ' ';
} else {
    $query .= 'ORDER BY inventory_order_id DESC ';
}

// Add limit condition
if ($_POST["length"] != -1) {
    $query .= 'LIMIT :start, :length';
}

// Prepare and execute the SQL query
$statement = $connect->prepare($query);
$statement->bindParam(':user_id', $_SESSION["user_id"], PDO::PARAM_INT);
$statement->bindParam(':search_value', $search_value, PDO::PARAM_STR);
$statement->bindParam(':start', $_POST['start'], PDO::PARAM_INT);
$statement->bindParam(':length', $_POST['length'], PDO::PARAM_INT);
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);

// Process the fetched data
foreach ($result as $row) {
    // Format the payment status
    $payment_status = ($row['payment_status'] == 'cash') ? '<span class="label label-primary">Cash</span>' : '<span class="label label-warning">Credit</span>';

    // Format the order status
    $status = ($row['inventory_order_status'] == 'active') ? '<span class="label label-success">Active</span>' : '<span class="label label-danger">Inactive</span>';

    // Prepare the sub-array for the DataTable
    $sub_array = array(
        $row['inventory_order_id'],
        $row['inventory_order_name'],
        $row['inventory_order_total'],
        $payment_status,
        $status,
        $row['inventory_order_date'],
    );

    // Add extra columns based on user type
    if ($_SESSION['type'] == '') {
        $sub_array[] = get_user_name($connect, $row['user_id']);
    }

    // Add action buttons
    $sub_array[] = '<a href="view_order.php?pdf=1&order_id=' . $row["inventory_order_id"] . '" class="btn btn-info btn-xs">View PDF</a>';
    $sub_array[] = '<button type="button" name="update" id="' . $row["inventory_order_id"] . '" class="btn btn-warning btn-xs update">Update</button>';
    $sub_array[] = '<button type="button" name="delete" id="' . $row["inventory_order_id"] . '" class="btn btn-danger btn-xs delete" data-status="' . $row["inventory_order_status"] . '">Delete</button>';

    // Add the sub-array to the data array
    $data[] = $sub_array;
}

// Calculate the total number of records
$total_records = get_total_all_records($connect);

// Prepare the output array
$output = array(
    "draw"            => intval($_POST["draw"]),
    "recordsTotal"    => $total_records,
    "recordsFiltered" => count($data),
    "data"            => $data
);

// Output the JSON data
echo json_encode($output);
?>
