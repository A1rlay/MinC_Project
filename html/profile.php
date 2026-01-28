<?php
// Check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /pages/MinC_Project/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - MinC</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/ca30ddfff9.js" crossorigin="anonymous"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .profile-gradient {
            background: linear-gradient(135deg, #08415c 0%, #0a5273 50%, #08415c 100%);
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #08415c 0%, #0a5273 100%);
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(8, 65, 92, 0.4);
        }
        
        .avatar-wrapper {
            position: relative;
            display: inline-block;
        }
        
        .avatar-wrapper:hover .edit-badge {
            opacity: 1;
        }
        
        .edit-badge {
            position: absolute;
            bottom: 0;
            right: 0;
            background: linear-gradient(135deg, #08415c 0%, #0a5273 100%);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            cursor: pointer;
            border: 3px solid white;
        }
        
        .nav-link-custom {
            position: relative;
            transition: color 0.3s ease;
        }
        
        .nav-link-custom:hover {
            color: #08415c;
        }
        
        .nav-link-custom::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: #08415c;
            transition: width 0.3s ease;
        }
        
        .nav-link-custom:hover::after {
            width: 100%;
        }
    </style>
</head>
<body class="bg-gray-50">
    
    <!-- Navigation Component -->
    <?php include 'components/navbar.php'; ?>
    
    <!-- Main Content -->
    <div class="mt-20 min-h-screen bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 py-8">
            
            <!-- Alert Messages -->
            <div id="alertBox" class="mb-6"></div>
            
            <!-- Profile Header Card -->
            <div class="bg-white rounded-xl shadow-lg mb-8 overflow-hidden">
                <div class="profile-gradient py-12 px-8 text-white">
                    <div class="flex flex-col md:flex-row items-center gap-8">
                        <!-- Avatar Section -->
                        <div class="avatar-wrapper flex-shrink-0">
                            <img id="profilePictureDisplay" 
                                 src="/pages/MinC_Project/Assets/images/default-avatar.png" 
                                 alt="Profile Picture" 
                                 class="w-32 h-32 rounded-full border-4 border-white object-cover shadow-lg">
                            <div class="edit-badge cursor-pointer" onclick="document.getElementById('profilePictureInput').click()">
                                <i class="fas fa-camera text-2xl"></i>
                            </div>
                            <input type="file" id="profilePictureInput" accept="image/jpeg,image/png,image/webp" class="hidden">
                        </div>
                        
                        <!-- Info Section -->
                        <div class="flex-1 text-center md:text-left">
                            <h1 id="profileFullName" class="text-4xl font-bold mb-2">Loading...</h1>
                            <p id="profileEmail" class="text-xl text-blue-100 mb-3">-</p>
                            <div class="flex flex-wrap gap-3 justify-center md:justify-start">
                                <span id="profileStatus" class="bg-green-400 text-gray-900 px-4 py-2 rounded-full font-semibold text-sm">Active</span>
                                <span class="bg-blue-400 bg-opacity-30 text-blue-100 px-4 py-2 rounded-full font-semibold text-sm flex items-center">
                                    <i class="fas fa-calendar mr-2"></i>
                                    <span id="profileMemberDate">Member</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Edit Profile Form Card -->
            <form id="profileForm" class="bg-white rounded-xl shadow-lg p-8">
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-edit text-[#08415c] mr-3"></i>
                        Edit Profile Information
                    </h2>
                </div>
                
                <!-- Form Grid -->
                <div class="grid md:grid-cols-2 gap-6 mb-6">
                    <!-- First Name -->
                    <div>
                        <label for="fname" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user mr-2 text-[#08415c]"></i>First Name *
                        </label>
                        <input type="text" id="fname" name="fname" required
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-[#08415c] transition"
                               placeholder="Enter first name">
                    </div>
                    
                    <!-- Last Name -->
                    <div>
                        <label for="lname" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user mr-2 text-[#08415c]"></i>Last Name *
                        </label>
                        <input type="text" id="lname" name="lname" required
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-[#08415c] transition"
                               placeholder="Enter last name">
                    </div>
                </div>
                
                <!-- Middle Name -->
                <div class="mb-6">
                    <label for="mname" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-user mr-2 text-[#08415c]"></i>Middle Name
                    </label>
                    <input type="text" id="mname" name="mname"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-[#08415c] transition"
                           placeholder="Enter middle name">
                </div>
                
                <!-- Email -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-[#08415c]"></i>Email Address
                    </label>
                    <input type="email" id="email" name="email" disabled
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg bg-gray-100 cursor-not-allowed text-gray-600">
                    <p class="text-xs text-gray-500 mt-2 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>Email cannot be changed from this page
                    </p>
                </div>
                
                <!-- Contact Number -->
                <div class="mb-8">
                    <label for="contact_num" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-phone mr-2 text-[#08415c]"></i>Contact Number
                    </label>
                    <input type="tel" id="contact_num" name="contact_num"
                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none focus:border-[#08415c] transition"
                           placeholder="+1 (555) 000-0000">
                </div>
                
                <!-- Picture Delete Button -->
                <div id="pictureBtnContainer" class="mb-8 hidden">
                    <button type="button" id="deletePictureBtn" 
                            class="w-full bg-red-500 hover:bg-red-600 text-white font-semibold py-3 rounded-lg transition flex items-center justify-center">
                        <i class="fas fa-trash mr-2"></i>Delete Profile Picture
                    </button>
                </div>
                
                <!-- Action Buttons -->
                <div class="grid md:grid-cols-2 gap-4">
                    <button type="submit" class="btn-primary-custom text-white font-semibold py-3 rounded-lg flex items-center justify-center">
                        <i class="fas fa-save mr-2"></i>Save Changes
                    </button>
                    <button type="reset" class="border-2 border-gray-300 text-gray-700 font-semibold py-3 rounded-lg hover:bg-gray-50 transition flex items-center justify-center">
                        <i class="fas fa-redo mr-2"></i>Reset Form
                    </button>
                </div>
                
                <!-- Loading State -->
                <div id="loading" class="hidden mt-4 flex items-center justify-center">
                    <div class="w-5 h-5 border-3 border-gray-300 border-t-[#08415c] rounded-full animate-spin mr-3"></div>
                    <span class="text-gray-600 font-medium">Saving changes...</span>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle mobile menu
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }
        
        // Handle logout
        function handleLogout() {
            if (confirm('Are you sure you want to logout?')) {
                fetch('/pages/MinC_Project/backend/logout.php')
                    .then(() => {
                        window.location.href = '/pages/MinC_Project/index.php';
                    });
            }
        }
        
        // Load profile data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadProfile();
            document.getElementById('profileForm').addEventListener('submit', handleProfileUpdate);
            document.getElementById('profilePictureInput').addEventListener('change', handleProfilePictureUpload);
            document.getElementById('deletePictureBtn').addEventListener('click', handleDeletePicture);
        });

        function showAlert(message, type = 'info') {
            const alertBox = document.getElementById('alertBox');
            const bgColor = type === 'success' ? 'bg-green-100' : type === 'error' ? 'bg-red-100' : 'bg-blue-100';
            const textColor = type === 'success' ? 'text-green-800' : type === 'error' ? 'text-red-800' : 'text-blue-800';
            const borderColor = type === 'success' ? 'border-green-400' : type === 'error' ? 'border-red-400' : 'border-blue-400';
            const icon = type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle';
            
            alertBox.innerHTML = `
                <div class="${bgColor} ${textColor} ${borderColor} border-l-4 p-4 rounded-lg flex items-center">
                    <i class="fas fa-${icon} mr-3"></i>
                    <span>${message}</span>
                </div>
            `;

            if (type === 'success') {
                setTimeout(() => {
                    alertBox.innerHTML = '';
                }, 4000);
            }
        }

        function loadProfile() {
            fetch('/pages/MinC_Project/backend/get_profile.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const user = data.data;

                        document.getElementById('profileFullName').textContent = `${user.fname} ${user.lname}`;
                        document.getElementById('profileEmail').textContent = user.email;
                        document.getElementById('profileStatus').textContent = user.user_status === 'active' ? 'Active' : 'Inactive';
                        document.getElementById('profileStatus').className = user.user_status === 'active' 
                            ? 'bg-green-400 text-gray-900 px-4 py-2 rounded-full font-semibold text-sm' 
                            : 'bg-red-400 text-white px-4 py-2 rounded-full font-semibold text-sm';
                        
                        const memberDate = new Date(user.created_at).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
                        document.getElementById('profileMemberDate').textContent = memberDate;

                        document.getElementById('fname').value = user.fname;
                        document.getElementById('lname').value = user.lname;
                        document.getElementById('mname').value = user.mname || '';
                        document.getElementById('email').value = user.email;
                        document.getElementById('contact_num').value = user.contact_num || '';

                        if (user.profile_picture_url) {
                            document.getElementById('profilePictureDisplay').src = user.profile_picture_url;
                            document.getElementById('pictureBtnContainer').classList.remove('hidden');
                        }
                    } else {
                        showAlert('Error loading profile: ' + (data.message || 'Unknown error'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Failed to load profile', 'error');
                });
        }

        function handleProfileUpdate(e) {
            e.preventDefault();

            const fname = document.getElementById('fname').value.trim();
            const lname = document.getElementById('lname').value.trim();
            const mname = document.getElementById('mname').value.trim();
            const contact_num = document.getElementById('contact_num').value.trim();

            if (!fname || !lname) {
                showAlert('First name and last name are required', 'error');
                return;
            }

            document.getElementById('loading').classList.remove('hidden');

            fetch('/pages/MinC_Project/backend/update_profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    fname: fname,
                    lname: lname,
                    mname: mname || null,
                    contact_num: contact_num || null
                })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loading').classList.add('hidden');

                if (data.success) {
                    showAlert('Profile updated successfully', 'success');
                    document.getElementById('profileFullName').textContent = `${fname} ${lname}`;
                } else {
                    showAlert('Error: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                document.getElementById('loading').classList.add('hidden');
                console.error('Error:', error);
                showAlert('Failed to update profile', 'error');
            });
        }

        function handleProfilePictureUpload(e) {
            const file = e.target.files[0];

            if (!file) return;

            if (file.size > 5 * 1024 * 1024) {
                showAlert('File size must be less than 5MB', 'error');
                e.target.value = '';
                return;
            }

            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                showAlert('Only JPG, PNG, and WebP images are allowed', 'error');
                e.target.value = '';
                return;
            }

            const formData = new FormData();
            formData.append('profile_picture', file);

            fetch('/pages/MinC_Project/backend/upload_profile_picture.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('profilePictureDisplay').src = data.data.picture_url + '?t=' + Date.now();
                    document.getElementById('pictureBtnContainer').classList.remove('hidden');
                    showAlert('Profile picture updated successfully', 'success');
                    e.target.value = '';
                } else {
                    showAlert('Error: ' + (data.message || 'Upload failed'), 'error');
                    e.target.value = '';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Failed to upload profile picture', 'error');
                e.target.value = '';
            });
        }

        function handleDeletePicture() {
            if (!confirm('Are you sure you want to delete your profile picture?')) {
                return;
            }

            fetch('/pages/MinC_Project/backend/delete_profile_picture.php', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('profilePictureDisplay').src = '/pages/MinC_Project/Assets/images/default-avatar.png';
                    document.getElementById('pictureBtnContainer').classList.add('hidden');
                    showAlert('Profile picture deleted successfully', 'success');
                } else {
                    showAlert('Error: ' + (data.message || 'Delete failed'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Failed to delete profile picture', 'error');
            });
        }
    </script>

    <!-- Footer Component -->
    <?php include 'components/footer.php'; ?>
</body>
</html>
