<?php
require_once 'config.php';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save'])) {
        // Create or Update
        $data = [
            'full_name' => $_POST['full_name'],
            'age' => $_POST['age'],
            'birthday' => $_POST['birthday'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'],
            'gender' => $_POST['gender'],
            'year_level' => $_POST['year_level'],
            'program' => $_POST['program'],
            'enrollment_date' => $_POST['enrollment_date']
        ];
        
        if (!empty($_POST['id'])) {
            // Update
            $data['id'] = $_POST['id'];
            $sql = "UPDATE students SET 
                    full_name = :full_name, 
                    age = :age, 
                    birthday = :birthday, 
                    email = :email, 
                    phone = :phone, 
                    gender = :gender, 
                    year_level = :year_level, 
                    program = :program, 
                    enrollment_date = :enrollment_date 
                    WHERE id = :id";
        } else {
            // Create
            $sql = "INSERT INTO students (full_name, age, birthday, email, phone, gender, year_level, program, enrollment_date) 
                    VALUES (:full_name, :age, :birthday, :email, :phone, :gender, :year_level, :program, :enrollment_date)";
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
    } elseif (isset($_POST['delete'])) {
        // Delete
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$_POST['delete']]);
    }
    
    header("Location: index.php");
    exit();
}

// Fetch all students
$search = $_GET['search'] ?? '';
$query = "SELECT * FROM students";
$params = [];

if (!empty($search)) {
    $query .= " WHERE full_name LIKE ? OR program LIKE ? OR year_level LIKE ? OR email LIKE ?";
    $searchTerm = "%$search%";
    $params = array_fill(0, 4, $searchTerm);
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch student for editing
$editStudent = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editStudent = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌸 Student Enrollment System</title>
    <style>
        :root {
            --primary-pink: #ffb6c1;
            --secondary-pink: #ffc0cb;
            --dark-pink: #db7093;
            --light-pink: #ffe4e1;
            --white: #fffafa;
            --text-color: #5a3e36;
        }
        
        body {
            font-family: 'Comic Sans MS', cursive, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--light-pink);
            color: var(--text-color);
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .container {
            flex: 1;
            display: flex;
            flex-direction: column;
            margin: 0;
            padding: 20px;
            background-color: var(--white);
            overflow: auto;
        }
        
        h1, h2 {
            color: var(--dark-pink);
            text-align: center;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            background-color: var(--primary-pink);
            padding: 15px;
            border-radius: 15px;
            color: white;
            border: 3px dotted var(--white);
        }
        
        h2 {
            font-size: 1.8em;
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 2px dashed var(--secondary-pink);
        }
        
        .content-wrapper {
            display: flex;
            flex: 1;
            gap: 20px;
            overflow: hidden;
        }
        
        .form-section {
            flex: 1;
            min-width: 400px;
            padding: 20px;
            background-color: var(--white);
            border-radius: 15px;
            border: 1px solid var(--secondary-pink);
            box-shadow: inset 0 0 10px rgba(255, 182, 193, 0.3);
            overflow-y: auto;
        }
        
        .list-section {
            flex: 2;
            padding: 20px;
            background-color: var(--white);
            border-radius: 15px;
            border: 1px solid var(--secondary-pink);
            box-shadow: inset 0 0 10px rgba(255, 182, 193, 0.3);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 15px;
            gap: 15px;
        }
        
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--dark-pink);
        }
        
        input, select {
            width: 100%;
            padding: 10px;
            border: 2px solid var(--secondary-pink);
            border-radius: 10px;
            box-sizing: border-box;
            background-color: var(--white);
            font-family: inherit;
            transition: all 0.3s;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: var(--dark-pink);
            box-shadow: 0 0 8px var(--primary-pink);
        }
        
        .buttons {
            margin-top: 25px;
            text-align: center;
        }
        
        button, .btn {
            padding: 10px 20px;
            margin: 0 10px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: bold;
            font-family: inherit;
            transition: all 0.3s;
            font-size: 1em;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .btn-primary {
            background-color: var(--dark-pink);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #c45e82;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: var(--secondary-pink);
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #ffa7b5;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background-color: #ff6b88;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #ff4757;
            transform: translateY(-2px);
        }
        
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            border-radius: 15px;
            overflow: hidden;
            flex: 1;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--secondary-pink);
        }
        
        th {
            background-color: var(--primary-pink);
            color: white;
            font-weight: bold;
            position: sticky;
            top: 0;
        }
        
        tr:nth-child(even) {
            background-color: var(--light-pink);
        }
        
        tr:hover {
            background-color: rgba(255, 182, 193, 0.2);
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .action-buttons .btn {
            padding: 5px 10px;
            font-size: 0.9em;
            margin: 0;
        }
        
        .search-bar {
            margin-bottom: 20px;
            text-align: center;
        }
        
        .search-bar input {
            padding: 10px 15px;
            width: 60%;
            max-width: 400px;
            border-radius: 50px;
            border: 2px solid var(--secondary-pink);
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        
        .modal-content {
            background-color: var(--white);
            margin: 15% auto;
            padding: 25px;
            border-radius: 20px;
            width: 80%;
            max-width: 400px;
            text-align: center;
            border: 3px solid var(--dark-pink);
            box-shadow: 0 0 20px rgba(219, 112, 147, 0.5);
        }
        
        .modal h3 {
            color: var(--dark-pink);
            margin-top: 0;
        }
        
        .modal-buttons {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        /* Decorative elements */
        .header-decoration {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .header-decoration span {
            font-size: 1.5em;
            margin: 0 5px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .content-wrapper {
                flex-direction: column;
            }
            
            .form-section, .list-section {
                min-width: 100%;
            }
            
            .form-row {
                flex-direction: column;
                gap: 10px;
            }
            
            .form-group {
                min-width: 100%;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-decoration">
            <span>🌸</span><span>✏️</span><span>📚</span>
        </div>
        <h1>Student Enrollment System</h1>
        
        <div class="content-wrapper">
            <div class="form-section">
                <h2>Student Information Form</h2>
                <form method="post">
                    <input type="hidden" name="id" value="<?= $editStudent['id'] ?? '' ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" 
                                   value="<?= htmlspecialchars($editStudent['full_name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="age">Age</label>
                            <input type="number" id="age" name="age" min="15" max="99" 
                                   value="<?= $editStudent['age'] ?? '' ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="birthday">Birthday</label>
                            <input type="date" id="birthday" name="birthday" 
                                   value="<?= $editStudent['birthday'] ?? '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" 
                                   value="<?= htmlspecialchars($editStudent['email'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" 
                                   value="<?= htmlspecialchars($editStudent['phone'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male" <?= isset($editStudent) && $editStudent['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= isset($editStudent) && $editStudent['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                                <option value="Other" <?= isset($editStudent) && $editStudent['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="year_level">Year Level</label>
                            <select id="year_level" name="year_level" required>
                                <option value="">Select Year Level</option>
                                <option value="1st Year" <?= isset($editStudent) && $editStudent['year_level'] === '1st Year' ? 'selected' : '' ?>>1st Year</option>
                                <option value="2nd Year" <?= isset($editStudent) && $editStudent['year_level'] === '2nd Year' ? 'selected' : '' ?>>2nd Year</option>
                                <option value="3rd Year" <?= isset($editStudent) && $editStudent['year_level'] === '3rd Year' ? 'selected' : '' ?>>3rd Year</option>
                                <option value="4th Year" <?= isset($editStudent) && $editStudent['year_level'] === '4th Year' ? 'selected' : '' ?>>4th Year</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="program">Program</label>
                            <select id="program" name="program" required>
                                <option value="">Select Program</option>
                                <option value="Information Technology" <?= isset($editStudent) && $editStudent['program'] === 'Information Technology' ? 'selected' : '' ?>>Information Technology</option>
                                <option value="Business Administration" <?= isset($editStudent) && $editStudent['program'] === 'Business Administration' ? 'selected' : '' ?>>Business Administration</option>
                                <option value="Computer Engineering" <?= isset($editStudent) && $editStudent['program'] === 'Computer Engineering' ? 'selected' : '' ?>>Computer Engineering</option>
                                <option value="Nursing" <?= isset($editStudent) && $editStudent['program'] === 'Nursing' ? 'selected' : '' ?>>Nursing</option>
                                <option value="Secondary Education" <?= isset($editStudent) && $editStudent['program'] === 'Secondary Education' ? 'selected' : '' ?>>Secondary Education</option>
                                <option value="Tourism" <?= isset($editStudent) && $editStudent['program'] === 'Tourism' ? 'selected' : '' ?>>Tourism</option>
                                <option value="Psychology" <?= isset($editStudent) && $editStudent['program'] === 'Psychology' ? 'selected' : '' ?>>Psychology</option>
                                <option value="Architecture" <?= isset($editStudent) && $editStudent['program'] === 'Architecture' ? 'selected' : '' ?>>Architecture</option>
                                <option value="Accountancy" <?= isset($editStudent) && $editStudent['program'] === 'Accountancy' ? 'selected' : '' ?>>Accountancy</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="enrollment_date">Enrollment Date</label>
                            <input type="date" id="enrollment_date" name="enrollment_date" 
                                   value="<?= $editStudent['enrollment_date'] ?? '' ?>" required>
                        </div>
                    </div>
                    
                    <div class="buttons">
                        <button type="submit" class="btn-primary" name="save" id="saveBtn">💾 Save Student</button>
                        <button type="button" class="btn-secondary" id="clearBtn">🧹 Clear Form</button>
                    </div>
                </form>
            </div>
            
            <div class="list-section">
                <h2>Enrolled Students</h2>
                <div class="search-bar">
                    <form method="get" action="index.php">
                        <input type="text" id="search" name="search" placeholder="🔍 Search students..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </form>
                </div>
                <table id="studentsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Age</th>
                            <th>Program</th>
                            <th>Year Level</th>
                            <th>Enrollment Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="studentsList">
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No students found 🎓</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?= $student['id'] ?></td>
                                    <td><?= htmlspecialchars($student['full_name']) ?></td>
                                    <td><?= $student['age'] ?></td>
                                    <td><?= htmlspecialchars($student['program']) ?></td>
                                    <td><?= $student['year_level'] ?></td>
                                    <td><?= $student['enrollment_date'] ?></td>
                                    <td class="action-buttons">
                                        <a href="index.php?edit=<?= $student['id'] ?>" class="btn btn-primary">✏️ Edit</a>
                                        <button type="button" class="btn btn-danger delete-btn" data-id="<?= $student['id'] ?>">🗑️ Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Deletion</h3>
            <p>Are you sure you want to delete this student? 🥺</p>
            <div class="modal-buttons">
                <form id="deleteForm" method="post" style="display: inline;">
                    <input type="hidden" name="delete" id="deleteId" value="">
                    <button type="submit" id="confirmDelete" class="btn-danger">✅ Yes, Delete</button>
                </form>
                <button id="cancelDelete" class="btn-secondary">❌ Cancel</button>
            </div>
        </div>
    </div>

    <script>
        // Delete confirmation modal functionality
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.delete-btn');
            const deleteModal = document.getElementById('deleteModal');
            const deleteForm = document.getElementById('deleteForm');
            const deleteIdInput = document.getElementById('deleteId');
            const cancelDelete = document.getElementById('cancelDelete');
            
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const studentId = this.getAttribute('data-id');
                    deleteIdInput.value = studentId;
                    deleteModal.style.display = 'block';
                });
            });
            
            cancelDelete.addEventListener('click', function() {
                deleteModal.style.display = 'none';
            });
            
            window.addEventListener('click', function(event) {
                if (event.target === deleteModal) {
                    deleteModal.style.display = 'none';
                }
            });
            
            // Clear form button functionality
            document.getElementById('clearBtn').addEventListener('click', function() {
                document.querySelector('form').reset();
                window.location.href = 'index.php';
            });
        });
    </script>
</body>
</html>