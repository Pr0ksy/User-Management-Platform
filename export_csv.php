<?php 

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=korisnici.csv');

include('db_config.php');

$output = fopen('php://output', 'w');

fwrite($output, "\xEF\xBB\xBF");

fputcsv($output, array('ID', 'Korisničko ime', 'Puno ime', 'Email', 'Uloga', 'Status plaćanja', 'Status naloga'));

$query = "SELECT id, username, full_name, email, role, is_paid, is_banned FROM users";
$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $status_placanja = $row['is_paid'] ? 'Plaćeno' : 'Nije plaćeno';
    $status_naloga = $row['is_banned'] ? 'Banovan' : 'Aktivan';

    fputcsv($output, array(
        $row['id'],
        $row['username'],
        $row['full_name'],
        $row['email'],
        $row['role'],
        $status_placanja,
        $status_naloga
    ));
}

fclose($output);
?>
