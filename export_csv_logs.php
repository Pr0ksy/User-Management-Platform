<?php 

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=logs.csv');

include('db_config.php');

$output = fopen('php://output', 'w');

fwrite($output, "\xEF\xBB\xBF");

fputcsv($output, array('ID', 'Korisničko ime', 'IP Adresa', 'Vreme prijave', 'Status'));

$query = "SELECT id, username, ip_address, login_time, success FROM user_logs";
$result = $conn->query($query);

if (!$result) {
    die("Greška u upitu: " . $conn->error);
}


while ($row = $result->fetch_assoc()) {
    fputcsv($output, array(
        $row['id'],
        $row['username'],
        $row['ip_address'],
        $row['login_time'],
        $row['success'] == 1 ? 'Uspešno' : 'Neuspešno'
    ));
}

fclose($output);
?>
