<?php 

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=logs.csv');

include('db_config.php'); // Uključivanje baze

$output = fopen('php://output', 'w');

// Dodaje BOM za podršku UTF-8 u Excelu
fwrite($output, "\xEF\xBB\xBF");

// Upis zaglavlja kolona
fputcsv($output, array('ID', 'Korisničko ime', 'IP Adresa', 'Vreme prijave', 'Status'));

// SQL upit
$query = "SELECT id, username, ip_address, login_time, success FROM user_logs";
$result = $conn->query($query);

// Provera da li je upit uspešan
if (!$result) {
    die("Greška u upitu: " . $conn->error);
}

// Upis podataka u CSV
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
