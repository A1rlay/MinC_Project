<?php
/**
 * Generate Report Frontend
 * File: C:\xampp\htdocs\dmmmsu-extension\app\frontend\generate_report.php
 */

// Authentication and user data
include_once '../backend/auth.php';
requireAuth('../../index.php');

// Include database connection
include_once '../../database/connect_database.php';

$user_data = getCurrentUser();

// Set custom title for this page
$custom_title = 'Generate Report - DMMMSU Extension System';

// Update user array to match app.php format
$user = [
    'full_name' => $user_data['name'] ?? 'Guest User',
    'user_type' => $user_data['user_type'] ?? 'User',
    'department' => $user_data['department'] ?? 'General',
    'is_logged_in' => isset($user_data['id'])
];

// Fetch campuses and departments for filters
try {
    // Get all campuses
    $campuses_query = "SELECT DISTINCT campus_id, campus_name FROM campuses WHERE campus_status = 'active' ORDER BY campus_name";
    $campuses = $pdo->query($campuses_query)->fetchAll(PDO::FETCH_ASSOC);

    // Get all departments with their campus via users
    $departments_query = "
        SELECT DISTINCT
            d.department_id, 
            d.department_name, 
            c.campus_name,
            c.campus_id
        FROM departments d
        INNER JOIN users u ON d.department_id = u.dept_id
        INNER JOIN campuses c ON u.campus_id = c.campus_id
        WHERE d.department_status = 'active' AND c.campus_status = 'active'
        ORDER BY c.campus_name, d.department_name
    ";
    $departments = $pdo->query($departments_query)->fetchAll(PDO::FETCH_ASSOC);

    // Get all statuses
    $statuses_query = "SELECT status_id, status_name, research_status FROM statuses ORDER BY status_name";
    $statuses = $pdo->query($statuses_query)->fetchAll(PDO::FETCH_ASSOC);

    // Get extension types
    $extension_types_query = "SELECT extension_type_id, type_name FROM extension_types ORDER BY type_name";
    $extension_types = $pdo->query($extension_types_query)->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $campuses = [];
    $departments = [];
    $statuses = [];
    $extension_types = [];
    error_log("Generate Report query error: " . $e->getMessage());
}

// Additional styles for report page
$additional_styles = '
<style>
.report-filter {
    transition: all 0.3s ease;
}

.report-filter:focus {
    transform: translateY(-1px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15);
}

.generate-btn {
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #3B82F6, #1E40AF);
}

.generate-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
    background: linear-gradient(135deg, #2563EB, #1D4ED8);
}

.report-table {
    transition: all 0.3s ease;
}

.report-row:hover {
    background-color: rgba(59, 130, 246, 0.05);
    transform: translateX(4px);
}

.status-badge {
    transition: all 0.2s ease;
}

.status-badge:hover {
    transform: scale(1.05);
}

.export-btn {
    transition: all 0.3s ease;
}

.export-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.loading-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.report-summary-card {
    background: linear-gradient(135deg, #F8FAFC, #E2E8F0);
    border: 2px solid #E2E8F0;
    transition: all 0.3s ease;
}

.report-summary-card:hover {
    border-color: #3B82F6;
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.1);
}

.print-styles {
    @media print {
        .no-print { display: none !important; }
        .print-title { font-size: 24px; font-weight: bold; margin-bottom: 20px; }
        .report-table { border-collapse: collapse; width: 100%; }
        .report-table th, .report-table td { border: 1px solid #000; padding: 8px; }
    }
}
</style>
';

// Report content
ob_start();
?>

<!-- Page Header -->
<div class="professional-card rounded-xl p-6 mb-6 animate-fadeIn">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">
                📊 Generate Extension Report
            </h2>
            <p class="text-gray-600">
                Generate comprehensive reports on extension projects by college, institute, and status.
            </p>
        </div>
        <div class="hidden md:block">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-indigo-600 rounded-xl flex items-center justify-center animate-float">
                <i class="fas fa-chart-bar text-white text-2xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Report Filters -->
<div class="professional-card rounded-xl p-6 mb-6 animate-fadeIn">
    <h3 class="text-lg font-semibold text-gray-800 mb-6 flex items-center">
        <i class="fas fa-filter text-blue-600 mr-3"></i>
        Report Filters
    </h3>
    
    <form id="reportForm" class="space-y-6">
        <!-- Filter Row 1 -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Campus Filter -->
            <div>
                <label for="campus_filter" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-university text-gray-500 mr-2"></i>Campus/College
                </label>
                <select id="campus_filter" name="campus_filter" class="report-filter w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option value="">All Campuses</option>
                    <?php foreach ($campuses as $campus): ?>
                        <option value="<?php echo $campus['campus_id']; ?>">
                            <?php echo htmlspecialchars($campus['campus_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Department Filter -->
            <div>
                <label for="department_filter" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-building text-gray-500 mr-2"></i>Department/Institute
                </label>
                <select id="department_filter" name="department_filter" class="report-filter w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['department_id']; ?>" data-campus="<?php echo $dept['campus_name']; ?>">
                            <?php echo htmlspecialchars($dept['department_name']); ?> (<?php echo htmlspecialchars($dept['campus_name']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status_filter" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-tasks text-gray-500 mr-2"></i>Extension Status
                </label>
                <select id="status_filter" name="status_filter" class="report-filter w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option value="">All Statuses</option>
                    <optgroup label="Active Extensions">
                        <?php foreach ($statuses as $status): ?>
                            <?php if (in_array(strtolower($status['research_status']), ['active', 'ongoing', 'in_progress'])): ?>
                                <option value="<?php echo $status['status_id']; ?>" data-type="ongoing">
                                    <?php echo htmlspecialchars($status['status_name']); ?> (Ongoing)
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="Completed Extensions">
                        <?php foreach ($statuses as $status): ?>
                            <?php if (in_array(strtolower($status['research_status']), ['completed', 'finished'])): ?>
                                <option value="<?php echo $status['status_id']; ?>" data-type="completed">
                                    <?php echo htmlspecialchars($status['status_name']); ?> (Completed)
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="Proposed Extensions">
                        <?php foreach ($statuses as $status): ?>
                            <?php if (in_array(strtolower($status['research_status']), ['pending', 'draft', 'proposal'])): ?>
                                <option value="<?php echo $status['status_id']; ?>" data-type="proposal">
                                    <?php echo htmlspecialchars($status['status_name']); ?> (Proposal)
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="Other Statuses">
                        <?php foreach ($statuses as $status): ?>
                            <?php if (!in_array(strtolower($status['research_status']), ['active', 'ongoing', 'in_progress', 'completed', 'finished', 'pending', 'draft', 'proposal'])): ?>
                                <option value="<?php echo $status['status_id']; ?>" data-type="other">
                                    <?php echo htmlspecialchars($status['status_name']); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
            </div>
        </div>

        <!-- Filter Row 2 -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Extension Type Filter -->
            <div>
                <label for="type_filter" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-tag text-gray-500 mr-2"></i>Extension Type
                </label>
                <select id="type_filter" name="type_filter" class="report-filter w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                    <option value="">All Types</option>
                    <?php foreach ($extension_types as $type): ?>
                        <option value="<?php echo $type['extension_type_id']; ?>">
                            <?php echo htmlspecialchars($type['type_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Date Range From -->
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar-alt text-gray-500 mr-2"></i>Start Date From
                </label>
                <input type="date" id="date_from" name="date_from" class="report-filter w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
            </div>

            <!-- Date Range To -->
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar-alt text-gray-500 mr-2"></i>Start Date To
                </label>
                <input type="date" id="date_to" name="date_to" class="report-filter w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center justify-between gap-4 pt-6 border-t border-gray-200">
            <div class="flex flex-wrap gap-3">
                <button type="button" id="generateReport" class="generate-btn px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 flex items-center">
                    <i class="fas fa-chart-bar mr-2"></i>
                    <span class="button-text">Generate Report</span>
                    <div class="loading-spinner fas fa-spinner-third ml-2 hidden"></div>
                </button>
                <button type="button" id="clearFilters" class="px-6 py-3 bg-gray-500 text-white font-medium rounded-lg hover:bg-gray-600 flex items-center">
                    <i class="fas fa-eraser mr-2"></i>
                    Clear Filters
                </button>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <button type="button" id="exportExcel" class="export-btn px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 flex items-center" disabled>
                    <i class="fas fa-file-excel mr-2"></i>
                    Export Excel
                </button>
                <button type="button" id="exportPDF" class="export-btn px-6 py-3 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 flex items-center" disabled>
                    <i class="fas fa-file-pdf mr-2"></i>
                    Export PDF
                </button>
                <button type="button" id="printReport" class="export-btn px-6 py-3 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 flex items-center" disabled>
                    <i class="fas fa-print mr-2"></i>
                    Print Report
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Report Summary (Hidden initially) -->
<div id="reportSummary" class="professional-card rounded-xl p-6 mb-6 hidden fade-in">
    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-chart-pie text-green-600 mr-3"></i>
        Report Summary
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="report-summary-card p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-blue-600" id="totalExtensions">0</div>
            <div class="text-sm text-gray-600">Total Extensions</div>
        </div>
        <div class="report-summary-card p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-green-600" id="ongoingExtensions">0</div>
            <div class="text-sm text-gray-600">Ongoing</div>
        </div>
        <div class="report-summary-card p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-emerald-600" id="completedExtensions">0</div>
            <div class="text-sm text-gray-600">Completed</div>
        </div>
        <div class="report-summary-card p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-amber-600" id="proposalExtensions">0</div>
            <div class="text-sm text-gray-600">Proposals</div>
        </div>
        <div class="report-summary-card p-4 rounded-lg text-center">
            <div class="text-2xl font-bold text-purple-600" id="uniqueDepartments">0</div>
            <div class="text-sm text-gray-600">Departments</div>
        </div>
    </div>
</div>

<!-- Report Results -->
<div id="reportResults" class="professional-card rounded-xl p-6 hidden fade-in">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
            <i class="fas fa-table text-blue-600 mr-3"></i>
            Extension Report Results
        </h3>
        <div class="text-sm text-gray-600">
            <span id="reportTimestamp"></span>
        </div>
    </div>
    
    <!-- Print Header (Hidden on screen, shown in print) -->
    <div class="print-title hidden">
        <h1>DMMMSU Extension Projects Report</h1>
        <p id="printFilters"></p>
        <p>Generated on: <span id="printTimestamp"></span></p>
        <hr style="margin: 20px 0;">
    </div>
    
    <div class="overflow-x-auto">
        <table id="reportTable" class="report-table min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Extension Name
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Department/Institute
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Campus
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Type
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Start Date
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        End Date
                    </th>
                    <th class="no-print px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody id="reportTableBody" class="bg-white divide-y divide-gray-200">
                <!-- Report data will be populated here -->
            </tbody>
        </table>
    </div>
    
    <!-- No Results Message -->
    <div id="noResults" class="text-center py-12 hidden">
        <div class="text-gray-400 mb-4">
            <i class="fas fa-search text-6xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-600 mb-2">No extensions found</h3>
        <p class="text-gray-500">Try adjusting your filters or search criteria.</p>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const reportForm = document.getElementById('reportForm');
    const generateButton = document.getElementById('generateReport');
    const clearButton = document.getElementById('clearFilters');
    const exportExcelButton = document.getElementById('exportExcel');
    const exportPDFButton = document.getElementById('exportPDF');
    const printButton = document.getElementById('printReport');
    
    const campusFilter = document.getElementById('campus_filter');
    const departmentFilter = document.getElementById('department_filter');
    
    let reportData = [];

    // Campus filter change - filter departments
    campusFilter.addEventListener('change', function() {
        const selectedCampus = this.value;
        const departmentOptions = departmentFilter.querySelectorAll('option[data-campus]');
        
        departmentOptions.forEach(option => {
            if (selectedCampus === '' || option.dataset.campus === campusFilter.options[campusFilter.selectedIndex].text) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
        
        // Reset department selection if current selection is not visible
        if (departmentFilter.value && departmentFilter.options[departmentFilter.selectedIndex].style.display === 'none') {
            departmentFilter.value = '';
        }
    });

    // Generate Report
    generateButton.addEventListener('click', async function() {
        const formData = new FormData(reportForm);
        
        // Show loading state
        const buttonText = this.querySelector('.button-text');
        const spinner = this.querySelector('.loading-spinner');
        buttonText.textContent = 'Generating...';
        spinner.classList.remove('hidden');
        this.disabled = true;

        try {
            const response = await fetch('../report-backend/generate_report_data.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                reportData = result.data;
                displayReport(result);
                
                // Enable export buttons
                exportExcelButton.disabled = false;
                exportPDFButton.disabled = false;
                printButton.disabled = false;
                
                // Show report sections
                document.getElementById('reportSummary').classList.remove('hidden');
                document.getElementById('reportResults').classList.remove('hidden');
                
                // Scroll to results
                document.getElementById('reportResults').scrollIntoView({ behavior: 'smooth' });
            } else {
                showNotification('Error generating report: ' + result.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error generating report. Please try again.', 'error');
        } finally {
            // Reset button state
            buttonText.textContent = 'Generate Report';
            spinner.classList.add('hidden');
            this.disabled = false;
        }
    });

    // Clear Filters
    clearButton.addEventListener('click', function() {
        reportForm.reset();
        
        // Hide report sections
        document.getElementById('reportSummary').classList.add('hidden');
        document.getElementById('reportResults').classList.add('hidden');
        
        // Disable export buttons
        exportExcelButton.disabled = true;
        exportPDFButton.disabled = true;
        printButton.disabled = true;
        
        // Reset department filter visibility
        const departmentOptions = departmentFilter.querySelectorAll('option[data-campus]');
        departmentOptions.forEach(option => {
            option.style.display = 'block';
        });
    });

    // Export to Excel
    exportExcelButton.addEventListener('click', function() {
        exportReport('excel');
    });

// Export to PDF
exportPDFButton.addEventListener('click', function() {
    generatePDF();
});

function generatePDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'mm', 'a4'); // Landscape orientation
    
    // Get filter information
    const filters = [];
    if (campusFilter.value) filters.push(`Campus: ${campusFilter.options[campusFilter.selectedIndex].text}`);
    if (departmentFilter.value) filters.push(`Department: ${departmentFilter.options[departmentFilter.selectedIndex].text}`);
    if (document.getElementById('status_filter').value) filters.push(`Status: ${document.getElementById('status_filter').options[document.getElementById('status_filter').selectedIndex].text}`);
    if (document.getElementById('type_filter').value) filters.push(`Type: ${document.getElementById('type_filter').options[document.getElementById('type_filter').selectedIndex].text}`);
    if (document.getElementById('date_from').value) filters.push(`From: ${formatDate(document.getElementById('date_from').value)}`);
    if (document.getElementById('date_to').value) filters.push(`To: ${formatDate(document.getElementById('date_to').value)}`);
    
    const filterText = filters.length > 0 ? filters.join(', ') : 'No filters applied';
    
    // Header
    doc.setFontSize(18);
    doc.setTextColor(30, 64, 175); // Blue color
    doc.text('DMMMSU Extension Projects Report', 15, 15);
    
    doc.setFontSize(10);
    doc.setTextColor(107, 114, 128); // Gray color
    doc.text(`Generated on: ${new Date().toLocaleString()}`, 15, 22);
    doc.text(`Filters: ${filterText}`, 15, 27);
    doc.text(`Total Records: ${reportData.length}`, 15, 32);
    
    // Summary statistics
    const summary = document.getElementById('reportSummary');
    if (!summary.classList.contains('hidden')) {
        const stats = [
            `Total: ${document.getElementById('totalExtensions').textContent}`,
            `Ongoing: ${document.getElementById('ongoingExtensions').textContent}`,
            `Completed: ${document.getElementById('completedExtensions').textContent}`,
            `Proposals: ${document.getElementById('proposalExtensions').textContent}`,
            `Departments: ${document.getElementById('uniqueDepartments').textContent}`
        ].join(' | ');
        
        doc.setFontSize(9);
        doc.text(stats, 15, 37);
    }
    
    // Prepare table data
    const tableData = reportData.map(extension => [
        extension.extension_name,
        extension.department_name || 'N/A',
        extension.campus_name || 'N/A',
        extension.type_name || 'N/A',
        extension.status_name,
        extension.start_date ? formatDate(extension.start_date) : 'N/A',
        extension.end_date ? formatDate(extension.end_date) : 'N/A',
        extension.worker_count || '0'
    ]);
    
    // Generate table
    doc.autoTable({
        head: [['Extension Name', 'Department', 'Campus', 'Type', 'Status', 'Start Date', 'End Date', 'Workers']],
        body: tableData,
        startY: 42,
        theme: 'grid',
        styles: {
            fontSize: 8,
            cellPadding: 2,
            overflow: 'linebreak',
            halign: 'left'
        },
        headStyles: {
            fillColor: [59, 130, 246], // Blue
            textColor: 255,
            fontStyle: 'bold',
            halign: 'left'
        },
        alternateRowStyles: {
            fillColor: [249, 250, 251] // Light gray
        },
        columnStyles: {
            0: { cellWidth: 60 }, // Extension Name
            1: { cellWidth: 45 }, // Department
            2: { cellWidth: 40 }, // Campus
            3: { cellWidth: 30 }, // Type
            4: { cellWidth: 30 }, // Status
            5: { cellWidth: 25 }, // Start Date
            6: { cellWidth: 25 }, // End Date
            7: { cellWidth: 15 }  // Workers
        },
        margin: { left: 15, right: 15 },
        didDrawPage: function(data) {
            // Footer
            const pageCount = doc.internal.getNumberOfPages();
            const pageSize = doc.internal.pageSize;
            const pageHeight = pageSize.height || pageSize.getHeight();
            
            doc.setFontSize(8);
            doc.setTextColor(107, 114, 128);
            doc.text(
                `Page ${data.pageNumber} of ${pageCount}`,
                data.settings.margin.left,
                pageHeight - 10
            );
            doc.text(
                'DMMMSU Extension Management System',
                pageSize.width / 2,
                pageHeight - 10,
                { align: 'center' }
            );
        }
    });
    
    // Save the PDF
    const filename = `DMMMSU_Extension_Report_${new Date().toISOString().slice(0, 10)}.pdf`;
    doc.save(filename);
    
    showNotification('PDF report generated successfully!', 'success');
}

    // Print Report
    printButton.addEventListener('click', function() {
        printReport();
    });

    function displayReport(result) {
        // Update summary
        document.getElementById('totalExtensions').textContent = result.summary.total;
        document.getElementById('ongoingExtensions').textContent = result.summary.ongoing;
        document.getElementById('completedExtensions').textContent = result.summary.completed;
        document.getElementById('proposalExtensions').textContent = result.summary.proposals;
        document.getElementById('uniqueDepartments').textContent = result.summary.departments;

        // Update timestamp
        const timestamp = new Date().toLocaleString();
        document.getElementById('reportTimestamp').textContent = `Generated on ${timestamp}`;
        document.getElementById('printTimestamp').textContent = timestamp;

        // Update print filters summary
        updatePrintFilters();

        // Display table data
        const tableBody = document.getElementById('reportTableBody');
        const noResults = document.getElementById('noResults');

        if (result.data.length === 0) {
            tableBody.innerHTML = '';
            noResults.classList.remove('hidden');
        } else {
            noResults.classList.add('hidden');
            
            tableBody.innerHTML = result.data.map(extension => `
                <tr class="report-row hover:bg-gray-50 transition-all duration-200">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">${escapeHtml(extension.extension_name)}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${escapeHtml(extension.department_name)}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${escapeHtml(extension.campus_name)}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">${escapeHtml(extension.type_name || 'N/A')}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="status-badge inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusBadgeClass(extension.research_status)}">
                            ${escapeHtml(extension.status_name)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${extension.start_date ? formatDate(extension.start_date) : 'N/A'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${extension.end_date ? formatDate(extension.end_date) : 'N/A'}
                    </td>
                    <td class="no-print px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button onclick="viewExtensionDetails(${extension.extension_id})" class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </td>
                </tr>
            `).join('');
        }
    }

    function getStatusBadgeClass(researchStatus) {
        switch(researchStatus.toLowerCase()) {
            case 'active':
            case 'ongoing':
            case 'in_progress':
                return 'bg-green-100 text-green-800';
            case 'completed':
            case 'finished':
                return 'bg-blue-100 text-blue-800';
            case 'pending':
            case 'draft':
            case 'proposal':
                return 'bg-amber-100 text-amber-800';
            case 'cancelled':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }

    function updatePrintFilters() {
        const filters = [];
        if (campusFilter.value) filters.push(`Campus: ${campusFilter.options[campusFilter.selectedIndex].text}`);
        if (departmentFilter.value) filters.push(`Department: ${departmentFilter.options[departmentFilter.selectedIndex].text}`);
        if (document.getElementById('status_filter').value) filters.push(`Status: ${document.getElementById('status_filter').options[document.getElementById('status_filter').selectedIndex].text}`);
        if (document.getElementById('type_filter').value) filters.push(`Type: ${document.getElementById('type_filter').options[document.getElementById('type_filter').selectedIndex].text}`);
        if (document.getElementById('date_from').value) filters.push(`From: ${formatDate(document.getElementById('date_from').value)}`);
        if (document.getElementById('date_to').value) filters.push(`To: ${formatDate(document.getElementById('date_to').value)}`);
        
        document.getElementById('printFilters').textContent = filters.length > 0 ? `Filters: ${filters.join(', ')}` : 'No filters applied';
    }

    function exportReport(format) {
        const formData = new FormData(reportForm);
        formData.append('export_format', format);
        
        // Create a temporary form to submit
        const tempForm = document.createElement('form');
        tempForm.method = 'POST';
        tempForm.action = `../report-backend/export_report.php`;
        tempForm.target = '_blank';
        
        for (let [key, value] of formData.entries()) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            tempForm.appendChild(input);
        }
        
        document.body.appendChild(tempForm);
        tempForm.submit();
        document.body.removeChild(tempForm);
    }

    function printReport() {
        // Add print styles temporarily
        const printStyles = `
            <style>
                @media print {
                    .no-print { display: none !important; }
                    .print-title { display: block !important; font-size: 24px; font-weight: bold; margin-bottom: 20px; }
                    .report-table { border-collapse: collapse; width: 100%; }
                    .report-table th, .report-table td { border: 1px solid #000 !important; padding: 8px; }
                    body { -webkit-print-color-adjust: exact; }
                }
            </style>
        `;
        
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>DMMMSU Extension Report</title>
                    ${printStyles}
                </head>
                <body>
                    ${document.getElementById('reportResults').innerHTML}
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg ${
            type === 'error' ? 'bg-red-500 text-white' : 'bg-blue-500 text-white'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    // Global function for viewing extension details
    window.viewExtensionDetails = function(extensionId) {
        window.location.href = `extension-projects.php?view=${extensionId}`;
    };
});
</script>

<?php
$report_content = ob_get_clean();

// Set the content for app.php
$content = $report_content;

// Include the app.php layout
include 'app.php';
?>