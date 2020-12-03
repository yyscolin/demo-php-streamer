<?php

include('mysql_connections.php');

function search_database_by_query($type, $search_query, $itemsCount=5, $verbose=true) {
    global $con;

    $search_results = [];

    $search_query .= '%';
    if ($type == 'vid') {
        $columns = $verbose
            ? "id, title, release_date, duration"
            : "id, title as name";
        $sql_query = "select $columns
            from vids where id like ? and status=3 order by modify_timestamp desc limit 5";
        $stmt = $con->prepare($sql_query);
        $stmt->bind_param('s', $search_query);
    } else {
        $columns = $verbose
            ? "id, name_j, name_l, name_f"
            : "id, name_j as name";
        $sql_query = "select $columns from stars
            where display=1
            and (concat(name_f, ' ', name_l) like ? or concat(name_l, ' ', name_f) like ?)
            order by name_f limit $itemsCount";
        $stmt = $con->prepare($sql_query);
        $stmt->bind_param('ss', $search_query, $search_query);
    }
    
    $stmt->execute();
    $db_response = $stmt->get_result();
    while ($r = $db_response->fetch_object()) {
        array_push($search_results, $r);
    }

    return $search_results;
}

function search_database_by_id($type, $id) {
    global $con;

    $table = $type."s";
    $stmt = $con->prepare("select * from $table where id = ?");
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $db_response = $stmt->get_result();
    // if ($db_response->num_rows === 0) redirectToHomePage();

    $r = $db_response->fetch_object();
    return $r;
}
