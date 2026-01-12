<!-- teacher_add_student_form.php -->
<?php
session_start();
// Add at the VERY TOP of each PHP file
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Pragma: no-cache");
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher'){
    echo "<p class='text-danger'>Unauthorized</p>";
    exit();
}
?>
<div class="card p-4" style="max-width:600px;margin:auto;">
  <h3>Add New Student</h3>
  <form id="addStudentForm">
    <div class="mb-3">
      <label>Student Code</label>
      <input type="text" name="student_code" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Full Name</label>
      <input type="text" name="full_name" class="form-control" required>
    </div>
    <!-- <div class="mb-3">
      <label>Email</label>
      <input type="email" name="email" class="form-control">
    </div> -->
    <div class="mb-3">
      <label>Faculty</label>
      <input type="text" name="faculty" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Semester</label>
      <select name="semester" class="form-select" required>
        <option value="">--Select--</option>
        <?php for($i=1;$i<=8;$i++): ?>
        <option value="<?= $i ?>">Semester <?= $i ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Add Student</button>
  </form>
  <div id="studentMessage" class="mt-3"></div>
</div>

<script>
const form = document.getElementById('addStudentForm');
const messageDiv = document.getElementById('studentMessage');

form.addEventListener('submit', function(e){
  e.preventDefault();
  const formData = new FormData(form);

  fetch('add_student.php', { method: 'POST', body: formData })
  .then(res => res.json())
  .then(data => {
    if(data.status === 'success'){
      messageDiv.innerHTML = `<div class="alert alert-success">${data.message}<br>Username: ${data.username}<br>Password: ${data.password}</div>`;
      form.reset();
    } else {
      messageDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
    }
  })
  .catch(err => {
    messageDiv.innerHTML = `<div class="alert alert-danger">Error: ${err}</div>`;
  });
});
</script>
