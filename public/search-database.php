<?php

include('mysql_connections.php');

function search_database($type, $search_query, $itemsCount=5) {
    global $con;

    $search_results = [];

    $search_query .= '%';
    if ($type == 'vid') {
        $sql_query = "select id, title as name
            from vids where id like ? and status=3 order by modify_timestamp desc limit 5";
        $stmt = $con->prepare($sql_query);
        $stmt->bind_param('s', $search_query);
    } else {
        $sql_query = "select id, name_j as name from stars
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
