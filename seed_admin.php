<?php
// seed_users.php
// Jalankan sekali untuk membuat/overwrite user default.
// HAPUS file ini setelah selesai.

require __DIR__ . '/db.php'; // sesuaikan path jika perlu

// daftar akun yang mau dibuat: ['nama','email','plain_password','peran']
$pengguna = [
  ['Admin Lab',     'admin@lab.test',    'admin123',    'admin'],
  ['Admin Lab 2',   'admin2@lab.test',   'admin123',    'admin'],
  ['Peminjam Satu', 'peminjam@lab.test', 'peminjam123', 'peminjam'],
];

// output informatif
echo "<pre>Mulai seed pengguna...\n\n";

foreach ($pengguna as $u) {
  [$nama, $email, $plain, $peran] = $u;
  // buat hash aman
  $hash = password_hash($plain, PASSWORD_BCRYPT);

  // cek apakah sudah ada
  $stmt = $conn->prepare("SELECT id FROM pengguna WHERE email = ?");
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $res = $stmt->get_result();

  if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $id = $row['id'];
    $upd = $conn->prepare("UPDATE pengguna SET nama=?, sandi=?, peran=?, diblokir=0 WHERE id=?");
    $upd->bind_param('sssi', $nama, $hash, $peran, $id);
    $ok = $upd->execute();
    echo ($ok ? "Updated: " : "Gagal update: ") . "{$email} -> password plain: {$plain}\n";
  } else {
    $ins = $conn->prepare("INSERT INTO pengguna(nama,email,sandi,peran,diblokir) VALUES(?,?,?,?,0)");
    $ins->bind_param('ssss', $nama, $email, $hash, $peran);
    $ok = $ins->execute();
    echo ($ok ? "Inserted: " : "Gagal insert: ") . "{$email} -> password plain: {$plain}\n";
  }
}

echo "\nSelesai. Hapus file seed_users.php setelah diverifikasi.\n";
echo "Contoh login:\n - admin@lab.test / admin123\n - peminjam@lab.test / peminjam123\n</pre>";
