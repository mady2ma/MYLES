<?php
require_once 'config.php';
requireAuth();
$csrf_token = generateCsrfToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MYLES Inventory Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #2c3e50;
            color: white;
            padding: 10px 20px;
            border-radius: 5px 5px 0 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header h2 {
            margin: 0;
            font-size: 1.5em;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .user-info span {
            font-size: 1.1em;
        }
        .user-info a {
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .user-info a.profile-link {
            background-color: #3498db;
        }
        .user-info a.profile-link:hover {
            background-color: #2980b9;
        }
        .user-info a.logout-link {
            background-color: #e74c3c;
        }
        .user-info a.logout-link:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>MYLES Inventory Management System</h2>
            <div class="user-info">
                <span>Logged in as: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="profile.php" class="profile-link">Profile</a>
                <a href="login.php?logout=1" class="logout-link">Logout</a>
            </div>
        </div>
        
        <div class="tabs">
            <div class="tab active" onclick="switchTab('entry')">Stock Entry</div>
            <div class="tab" onclick="switchTab('issue')">Stock Issue</div>
            <div class="tab" onclick="switchTab('report')">Inventory Report</div>
            <div class="tab" onclick="switchTab('transactions')">Transactions</div>
            <div class="tab" onclick="switchTab('stats')">Statistics</div>
            <div class="tab" onclick="switchTab('backup')">Backup/Restore</div>
        </div>
        
        <!-- Rest of the content remains unchanged -->
        
        <!-- Rest of the content remains unchanged -->
        
        <!-- Stock Entry -->
        <div id="entry-tab" class="tab-content active">
            <label>Collection Name:</label>
            <input list="collections" id="collection" placeholder="Type or select">
            <datalist id="collections"></datalist>
            <label>Date:</label>
            <input type="date" id="entryDate">
            <label>Notes:</label>
            <textarea id="entryNotes" placeholder="Optional notes"></textarea>
            <table>
                <thead>
                    <tr>
                        <th>Size</th>
                        <th>S</th>
                        <th>M</th>
                        <th>L</th>
                        <th>XL</th>
                        <th>XXL</th>
                        <th>XXXL</th>
                        <th>Mix</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Quantity</td>
                        <td><input type="number" class="size-input" id="S" min="0" value="0"></td>
                        <td><input type="number" class="size-input" id="M" min="0" value="0"></td>
                        <td><input type="number" class="size-input" id="L" min="0" value="0"></td>
                        <td><input type="number" class="size-input" id="XL" min="0" value="0"></td>
                        <td><input type="number" class="size-input" id="XXL" min="0" value="0"></td>
                        <td><input type="number" class="size-input" id="XXXL" min="0" value="0"></td>
                        <td><input type="number" class="size-input" id="Mix" min="0" value="0"></td>
                    </tr>
                </tbody>
            </table>
            <div class="button-group">
                <button onclick="addEntry()">Add Stock</button>
                <button class="btn-danger" onclick="resetForm()">Reset</button>
                <button class="btn-warning" onclick="editLastEntry()">Edit Last</button>
            </div>
        </div>
        
        <!-- Stock Issue -->
        <div id="issue-tab" class="tab-content">
            <label>Collection Name:</label>
            <input list="collections" id="issueCollection" placeholder="Type or select">
            <label>Date:</label>
            <input type="date" id="issueDate">
            <label>Issued To:</label>
            <input type="text" id="issueTo" placeholder="Recipient">
            <label>Notes:</label>
            <textarea id="issueNotes" placeholder="Optional notes"></textarea>
            <table>
                <thead>
                    <tr>
                        <th>Size</th>
                        <th>S</th>
                        <th>M</th>
                        <th>L</th>
                        <th>XL</th>
                        <th>XXL</th>
                        <th>XXXL</th>
                        <th>Mix</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Quantity</td>
                        <td><input type="number" class="size-input" id="issueS" min="0" value="0"></td>
                        <td><input type="number" class="size-input" id="issueM" min="0" value="0"></td>
                        <td><input type="number" class="size-input" id="issueL" min="0" value="0"></td>
                        <td><input type="number" class="size-input" id="issueXL" min="0" value="0"></td>
                        <td><input type="number" class="size-input" id="issueXXL" min="0" value="0"></td>
                        <td><input type="number" class="size-input" id="issueXXXL" min="0" value="0"></td>
                        <td><input type="number" class="size-input" id="issueMix" min="0" value="0"></td>
                    </tr>
                </tbody>
            </table>
            <div class="button-group">
                <button onclick="issueStock()">Issue Stock</button>
                <button class="btn-danger" onclick="resetIssueForm()">Reset</button>
                <button class="btn-warning" onclick="editLastIssue()">Edit Last</button>
            </div>
        </div>
        
        <!-- Inventory Report -->
        <div id="report-tab" class="tab-content">
            <div class="search-container">
                <input type="search" id="searchInput" placeholder="Search collections..." oninput="updateReport()">
                <button class="btn-info" onclick="exportToExcel()">Export to CSV</button>
            </div>
            <table id="reportTable">
                <thead>
                    <tr>
                        <th>Collection</th>
                        <th>Last Updated</th>
                        <th>S</th>
                        <th>M</th>
                        <th>L</th>
                        <th>XL</th>
                        <th>XXL</th>
                        <th>XXXL</th>
                        <th>Mix</th>
                        <th class="total-cell">Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="reportBody"></tbody>
            </table>
        </div>
        
        <!-- Transactions -->
        <div id="transactions-tab" class="tab-content">
            <div class="date-range-selector">
                <label>From:</label><input type="date" id="startDate" onchange="updateTransactions()">
                <label>To:</label><input type="date" id="endDate" onchange="updateTransactions()">
                <button class="btn-info" onclick="updateTransactions()">Filter</button>
                <button onclick="resetTransactionFilter()">Reset</button>
            </div>
            <table id="transactionsTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Collection</th>
                        <th>S</th>
                        <th>M</th>
                        <th>L</th>
                        <th>XL</th>
                        <th>XXL</th>
                        <th>XXXL</th>
                        <th>Mix</th>
                        <th class="total-cell">Total</th>
                        <th>Details</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="transactionsBody"></tbody>
            </table>
        </div>
        
        <!-- Statistics -->
        <div id="stats-tab" class="tab-content">
            <div class="stats-container">
                <div class="stat-card"><h3>Total Collections</h3><div class="stat-value" id="totalCollections">0</div></div>
                <div class="stat-card"><h3>Total Items</h3><div class="stat-value" id="totalItems">0</div></div>
                <div class="stat-card"><h3>Most Stocked Size</h3><div class="stat-value" id="popularSize">-</div></div>
                <div class="stat-card"><h3>Last Updated</h3><div class="stat-value" id="lastUpdated">-</div></div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Size</th>
                        <th>Quantity</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody id="sizeDistributionBody"></tbody>
            </table>
        </div>

        <!-- Backup/Restore -->
        <div id="backup-tab" class="tab-content">
            <div class="backup-container">
                <button class="btn-info" onclick="backupDatabase()">Backup Database</button>
                <input type="file" id="restoreFile" accept=".sql">
                <button class="btn-warning" onclick="restoreDatabase()">Restore Database</button>
                <p id="backupStatus"></p>
            </div>
        </div>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">×</span>
            <h2 id="editTitle"></h2>
            <div id="editContent"></div>
        </div>
    </div>

    <div id="notification" class="notification"></div>
    <div class="footer">MYLES Inventory Management System v3.7 | Made By Manish Acharya</div>

    <script>
        const sizes = ['S', 'M', 'L', 'XL', 'XXL', 'XXXL', 'Mix'];
        const csrfToken = '<?php echo $csrf_token; ?>';

        // API Functions with CSRF token
        async function apiRequest(url, method = 'GET', body = null) {
            const headers = { 
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken 
            };
            const options = { method, headers };
            if (body) options.body = JSON.stringify(body);
            const response = await fetch(url, options);
            const text = await response.text();
            console.log(`${method} ${url} response:`, text);
            return JSON.parse(text);
        }

        async function fetchCollections() {
            try {
                const result = await apiRequest('api.php?action=collections');
                if (!result.success) throw new Error(result.error);
                return result.data;
            } catch (e) {
                console.error('fetchCollections error:', e);
                showNotification(`Error fetching collections: ${e.message}`, 'error');
                return [];
            }
        }

        async function fetchTransactions() {
            try {
                const result = await apiRequest('api.php?action=transactions');
                if (!result.success) throw new Error(result.error);
                return result.data;
            } catch (e) {
                console.error('fetchTransactions error:', e);
                showNotification(`Error fetching transactions: ${e.message}`, 'error');
                return [];
            }
        }

        async function fetchLastEntry() {
            try {
                const result = await apiRequest('api.php?action=last_entry');
                if (!result.success) throw new Error(result.error);
                return result.data;
            } catch (e) {
                console.error('fetchLastEntry error:', e);
                return null;
            }
        }

        async function fetchLastIssue() {
            try {
                const result = await apiRequest('api.php?action=last_issue');
                if (!result.success) throw new Error(result.error);
                return result.data;
            } catch (e) {
                console.error('fetchLastIssue error:', e);
                return null;
            }
        }

        async function saveCollection(data) {
            data.action = 'add_collection';
            const result = await apiRequest('api.php', 'POST', data);
            if (!result.success) throw new Error(result.error);
            return result;
        }

        async function updateCollection(name, data) {
            const result = await apiRequest(`api.php?name=${encodeURIComponent(name)}`, 'PUT', data);
            if (!result.success) throw new Error(result.error);
            return result;
        }

        async function updateTransaction(id, data) {
            const result = await apiRequest(`api.php?transaction_id=${id}`, 'PUT', data);
            if (!result.success) throw new Error(result.error);
            return result;
        }

        async function deleteCollection(name) {
            const result = await apiRequest(`api.php?name=${encodeURIComponent(name)}`, 'DELETE');
            if (!result.success) throw new Error(result.error);
            return result;
        }

        async function deleteTransaction(id) {
            const result = await apiRequest(`api.php?transaction_id=${id}`, 'DELETE');
            if (!result.success) throw new Error(result.error);
            return result;
        }

        async function saveTransaction(data) {
            data.action = 'add_transaction';
            const result = await apiRequest('api.php', 'POST', data);
            if (!result.success) throw new Error(result.error);
            return result;
        }

        // Helpers
        function calculateTotal(obj) {
            return sizes.reduce((sum, size) => sum + (parseInt(obj[size]) || 0), 0);
        }

        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification ${type}`;
            notification.style.display = 'block';
            setTimeout(() => notification.style.display = 'none', 3000);
        }

        async function updateAll() {
            await Promise.all([updateDatalist(), updateReport(), updateTransactions(), updateStatistics()]);
        }

        // Initialization
        document.addEventListener('DOMContentLoaded', async () => {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('entryDate').value = today;
            document.getElementById('issueDate').value = today;
            document.getElementById('startDate').value = today;
            document.getElementById('endDate').value = today;
            await updateAll();
        });

        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.getElementById(`${tabName}-tab`).classList.add('active');
            document.querySelector(`.tab[onclick="switchTab('${tabName}')"]`).classList.add('active');
            if (tabName === 'report') updateReport();
            if (tabName === 'transactions') updateTransactions();
            if (tabName === 'stats') updateStatistics();
            if (tabName === 'backup') document.getElementById('backupStatus').textContent = '';
        }

        // Stock Entry
        async function addEntry() {
            const collection = document.getElementById('collection').value.trim();
            const date = document.getElementById('entryDate').value || new Date().toISOString().split('T')[0];
            const notes = document.getElementById('entryNotes').value.trim();
            const quantities = {};
            sizes.forEach(size => quantities[size] = parseInt(document.getElementById(size).value) || 0);

            if (!collection) return showNotification('Enter a collection name', 'error');
            if (calculateTotal(quantities) === 0) return showNotification('Enter at least one quantity', 'error');

            try {
                const collections = await fetchCollections();
                const existing = collections.find(c => c.name.toLowerCase() === collection.toLowerCase());
                const total = calculateTotal(quantities);

                if (existing) {
                    const updatedQuantities = {};
                    sizes.forEach(size => updatedQuantities[size] = (existing[size] || 0) + quantities[size]);
                    await updateCollection(existing.name, { quantities: updatedQuantities, last_updated: date, notes });
                } else {
                    await saveCollection({ name: collection, created: date, last_updated: date, notes, quantities });
                }
                await saveTransaction({ type: 'entry', collection_name: collection, date, notes, quantities, timestamp: new Date().toISOString() });
                showNotification(`Added ${total} items to ${collection}`);
                resetForm();
                await updateAll();
            } catch (e) {
                showNotification(`Error adding entry: ${e.message}`, 'error');
            }
        }

        // Stock Issue
        async function issueStock() {
            const collection = document.getElementById('issueCollection').value.trim();
            const date = document.getElementById('issueDate').value || new Date().toISOString().split('T')[0];
            const issuedTo = document.getElementById('issueTo').value.trim();
            const notes = document.getElementById('issueNotes').value.trim();
            const quantities = {};
            sizes.forEach(size => quantities[size] = parseInt(document.getElementById(`issue${size}`).value) || 0);

            if (!collection) return showNotification('Enter a collection name', 'error');
            if (!issuedTo) return showNotification('Enter issued to', 'error');
            if (calculateTotal(quantities) === 0) return showNotification('Enter at least one quantity', 'error');

            try {
                const collections = await fetchCollections();
                const existing = collections.find(c => c.name.toLowerCase() === collection.toLowerCase());
                if (!existing) return showNotification('Collection not found', 'error');

                const total = calculateTotal(quantities);
                for (const size of sizes) {
                    if (quantities[size] > (existing[size] || 0)) return showNotification(`Not enough ${size} in stock`, 'error');
                }

                const updatedQuantities = {};
                sizes.forEach(size => updatedQuantities[size] = (existing[size] || 0) - quantities[size]);
                await updateCollection(existing.name, { quantities: updatedQuantities, last_updated: date, notes });
                await saveTransaction({ type: 'issue', collection_name: collection, date, issued_to: issuedTo, quantities, notes, timestamp: new Date().toISOString() });
                showNotification(`Issued ${total} items from ${collection}`);
                resetIssueForm();
                await updateAll();
            } catch (e) {
                showNotification(`Error issuing stock: ${e.message}`, 'error');
            }
        }

        // Edit Functions
        async function editLastEntry() {
            const lastEntry = await fetchLastEntry();
            if (!lastEntry) return showNotification('No last entry found', 'error');
            showEditModal('Edit Last Entry', lastEntry, saveLastEntryEdit);
        }

        async function editLastIssue() {
            const lastIssue = await fetchLastIssue();
            if (!lastIssue) return showNotification('No last issue found', 'error');
            showEditModal('Edit Last Issue', lastIssue, saveLastIssueEdit);
        }

        async function editTransaction(id) {
            const transactions = await fetchTransactions();
            const transaction = transactions.find(t => t.id == id);
            if (!transaction) return showNotification('Transaction not found', 'error');
            showEditModal(`Edit ${transaction.type === 'entry' ? 'Entry' : 'Issue'}`, transaction, saveTransactionEdit);
        }

        function showEditModal(title, data, saveFn) {
            document.getElementById('editTitle').textContent = title;
            const content = document.getElementById('editContent');
            content.innerHTML = `
                <label>Collection:</label>
                <input type="text" id="editCollection" value="${data.collection_name || ''}">
                <label>Date:</label>
                <input type="date" id="editDate" value="${data.date}">
                ${data.type === 'issue' ? '<label>Issued To:</label><input type="text" id="editIssuedTo" value="' + (data.issued_to || '') + '">' : ''}
                <label>Notes:</label>
                <textarea id="editNotes">${data.notes || ''}</textarea>
                <table>
                    <thead><tr><th>Size</th>${sizes.map(s => `<th>${s}</th>`).join('')}</tr></thead>
                    <tbody><tr><td>Quantity</td>${sizes.map(s => `<td><input type="number" class="size-input" id="edit${s}" value="${data[s] || 0}" min="0"></td>`).join('')}</tr></tbody>
                </table>
                <div class="button-group">
                    <button onclick="${saveFn.name}(${data.id})">Save</button>
                    <button class="btn-danger" onclick="deleteTransactionModal(${data.id}, '${data.type}')">Delete</button>
                </div>
            `;
            document.getElementById('editModal').style.display = 'block';
        }

        async function saveLastEntryEdit(id) {
            const collection = document.getElementById('editCollection').value.trim();
            const date = document.getElementById('editDate').value;
            const notes = document.getElementById('editNotes').value.trim();
            const quantities = {};
            sizes.forEach(size => quantities[size] = parseInt(document.getElementById(`edit${size}`).value) || 0);

            if (!collection || !date) return showNotification('Collection and date are required', 'error');
            if (calculateTotal(quantities) === 0) return showNotification('Enter at least one quantity', 'error');

            try {
                const collections = await fetchCollections();
                const existing = collections.find(c => c.name.toLowerCase() === collection.toLowerCase());
                const lastEntry = await fetchLastEntry();
                const total = calculateTotal(quantities);

                if (existing && existing.name !== lastEntry.collection_name) {
                    const updatedQuantities = {};
                    sizes.forEach(size => updatedQuantities[size] = (existing[size] || 0) + quantities[size]);
                    await updateCollection(existing.name, { quantities: updatedQuantities, last_updated: date, notes });
                } else if (!existing) {
                    await saveCollection({ name: collection, created: date, last_updated: date, notes, quantities });
                }

                if (lastEntry.collection_name !== collection) {
                    const oldCollection = collections.find(c => c.name === lastEntry.collection_name);
                    if (oldCollection) {
                        const revertedQuantities = {};
                        sizes.forEach(size => revertedQuantities[size] = (oldCollection[size] || 0) - (lastEntry[size] || 0));
                        await updateCollection(oldCollection.name, { quantities: revertedQuantities, last_updated: date, notes });
                    }
                } else if (existing) {
                    const updatedQuantities = {};
                    sizes.forEach(size => updatedQuantities[size] = (existing[size] || 0) - (lastEntry[size] || 0) + quantities[size]);
                    await updateCollection(existing.name, { quantities: updatedQuantities, last_updated: date, notes });
                }

                await updateTransaction(id, { type: 'entry', collection_name: collection, date, quantities, notes, timestamp: new Date().toISOString() });
                showNotification(`Updated entry: ${total} items`);
                closeEditModal();
                await updateAll();
            } catch (e) {
                showNotification(`Error updating entry: ${e.message}`, 'error');
            }
        }

        async function saveLastIssueEdit(id) {
            const collection = document.getElementById('editCollection').value.trim();
            const date = document.getElementById('editDate').value;
            const issuedTo = document.getElementById('editIssuedTo').value.trim();
            const notes = document.getElementById('editNotes').value.trim();
            const quantities = {};
            sizes.forEach(size => quantities[size] = parseInt(document.getElementById(`edit${size}`).value) || 0);

            if (!collection || !date || !issuedTo) return showNotification('Collection, date, and issued to are required', 'error');
            if (calculateTotal(quantities) === 0) return showNotification('Enter at least one quantity', 'error');

            try {
                const collections = await fetchCollections();
                const existing = collections.find(c => c.name.toLowerCase() === collection.toLowerCase());
                if (!existing) return showNotification('Collection not found', 'error');

                const lastIssue = await fetchLastIssue();
                const total = calculateTotal(quantities);
                const revertedQuantities = {};
                sizes.forEach(size => revertedQuantities[size] = (existing[size] || 0) + (lastIssue[size] || 0));
                for (const size of sizes) {
                    if (quantities[size] > revertedQuantities[size]) return showNotification(`Not enough ${size} in stock`, 'error');
                }

                const updatedQuantities = {};
                sizes.forEach(size => updatedQuantities[size] = revertedQuantities[size] - quantities[size]);
                await updateCollection(existing.name, { quantities: updatedQuantities, last_updated: date, notes });
                await updateTransaction(id, { type: 'issue', collection_name: collection, date, issued_to: issuedTo, quantities, notes, timestamp: new Date().toISOString() });
                showNotification(`Updated issue: ${total} items`);
                closeEditModal();
                await updateAll();
            } catch (e) {
                showNotification(`Error updating issue: ${e.message}`, 'error');
            }
        }

        async function saveTransactionEdit(id) {
            const collection = document.getElementById('editCollection').value.trim();
            const date = document.getElementById('editDate').value;
            const issuedTo = document.getElementById('editIssuedTo') ? document.getElementById('editIssuedTo').value.trim() : '';
            const notes = document.getElementById('editNotes').value.trim();
            const quantities = {};
            sizes.forEach(size => quantities[size] = parseInt(document.getElementById(`edit${size}`).value) || 0);

            try {
                const transactions = await fetchTransactions();
                const transaction = transactions.find(t => t.id == id);
                if (!transaction) return showNotification('Transaction not found', 'error');

                const isIssue = transaction.type === 'issue';
                if (!collection || !date) return showNotification('Collection and date are required', 'error');
                if (isIssue && !issuedTo) return showNotification('Issued to is required for issues', 'error');
                if (calculateTotal(quantities) === 0) return showNotification('Enter at least one quantity', 'error');

                const collections = await fetchCollections();
                const existing = collections.find(c => c.name.toLowerCase() === collection.toLowerCase());
                if (isIssue && !existing) return showNotification('Collection not found', 'error');

                const total = calculateTotal(quantities);
                if (existing) {
                    const oldQuantities = {};
                    sizes.forEach(size => oldQuantities[size] = transaction[size] || 0);
                    const revertedQuantities = {};
                    sizes.forEach(size => revertedQuantities[size] = (existing[size] || 0) + (isIssue ? oldQuantities[size] : -oldQuantities[size]));
                    
                    if (isIssue) {
                        for (const size of sizes) {
                            if (quantities[size] > revertedQuantities[size]) return showNotification(`Not enough ${size} in stock`, 'error');
                        }
                    }

                    const updatedQuantities = {};
                    sizes.forEach(size => updatedQuantities[size] = revertedQuantities[size] + (isIssue ? -quantities[size] : quantities[size]));
                    await updateCollection(existing.name, { quantities: updatedQuantities, last_updated: date, notes });
                } else if (!isIssue) {
                    await saveCollection({ name: collection, created: date, last_updated: date, notes, quantities });
                }

                if (transaction.collection_name !== collection) {
                    const oldCollection = collections.find(c => c.name === transaction.collection_name);
                    if (oldCollection) {
                        const revertedQuantities = {};
                        sizes.forEach(size => revertedQuantities[size] = (oldCollection[size] || 0) + (isIssue ? transaction[size] : -transaction[size] || 0));
                        await updateCollection(oldCollection.name, { quantities: revertedQuantities, last_updated: date, notes });
                    }
                }

                const updatedData = isIssue ? 
                    { type: 'issue', collection_name: collection, date, issued_to: issuedTo, quantities, notes, timestamp: new Date().toISOString() } :
                    { type: 'entry', collection_name: collection, date, quantities, notes, timestamp: new Date().toISOString() };
                await updateTransaction(id, updatedData);
                showNotification(`Updated ${isIssue ? 'issue' : 'entry'}: ${total} items`);
                closeEditModal();
                await updateAll();
            } catch (e) {
                showNotification(`Error updating transaction: ${e.message}`, 'error');
            }
        }

        async function deleteTransactionModal(id, type) {
            if (!confirm(`Delete this ${type}?`)) return;
            try {
                const transactions = await fetchTransactions();
                const transaction = transactions.find(t => t.id == id);
                if (!transaction) return showNotification('Transaction not found', 'error');

                const collections = await fetchCollections();
                const collection = collections.find(c => c.name === transaction.collection_name);
                if (collection) {
                    const updatedQuantities = {};
                    sizes.forEach(size => updatedQuantities[size] = (collection[size] || 0) + (type === 'issue' ? transaction[size] : -transaction[size] || 0));
                    await updateCollection(collection.name, { quantities: updatedQuantities, last_updated: new Date().toISOString().split('T')[0], notes: transaction.notes });
                }

                await deleteTransaction(id);
                showNotification(`${type.charAt(0).toUpperCase() + type.slice(1)} deleted`);
                closeEditModal();
                await updateAll();
            } catch (e) {
                showNotification(`Error deleting transaction: ${e.message}`, 'error');
            }
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // UI Updates
        async function updateDatalist() {
            const datalist = document.getElementById('collections');
            datalist.innerHTML = '';
            const collections = await fetchCollections();
            collections.forEach(c => {
                const option = document.createElement('option');
                option.value = c.name;
                datalist.appendChild(option);
            });
        }

        async function updateReport() {
            const reportBody = document.getElementById('reportBody');
            reportBody.innerHTML = '';
            const collections = await fetchCollections();
            const search = document.getElementById('searchInput').value.toLowerCase();

            collections.filter(c => !search || c.name.toLowerCase().includes(search)).forEach(c => {
                const total = calculateTotal(c);
                reportBody.innerHTML += `
                    <tr>
                        <td>${c.name}</td>
                        <td>${c.last_updated}</td>
                        ${sizes.map(s => `<td>${c[s] || 0}</td>`).join('')}
                        <td class="total-cell">${total}</td>
                        <td><button class="btn-danger" onclick="deleteCollectionPrompt('${c.name}')">Delete</button></td>
                    </tr>
                `;
            });
            if (!reportBody.innerHTML) reportBody.innerHTML = '<tr><td colspan="11">No collections found</td></tr>';
        }

        async function updateTransactions() {
            const transactionsBody = document.getElementById('transactionsBody');
            transactionsBody.innerHTML = '';
            const transactions = await fetchTransactions();
            const start = document.getElementById('startDate').value;
            const end = document.getElementById('endDate').value;

            transactions.filter(t => (!start || t.date >= start) && (!end || t.date <= end)).forEach(t => {
                const total = calculateTotal(t);
                transactionsBody.innerHTML += `
                    <tr>
                        <td>${t.date}</td>
                        <td>${t.type}</td>
                        <td>${t.collection_name || '-'}</td>
                        ${sizes.map(s => `<td class="${t.type === 'issue' ? 'negative-cell' : ''}">${t.type === 'issue' && t[s] > 0 ? -t[s] : t[s] || 0}</td>`).join('')}
                        <td class="total-cell">${t.type === 'issue' ? -total : total}</td>
                        <td>${t.issued_to ? `To: ${t.issued_to}` : ''}${t.notes ? ` | ${t.notes}` : ''}</td>
                        <td>
                            <button class="btn-warning" onclick="editTransaction(${t.id})">Edit</button>
                            <button class="btn-danger" onclick="deleteTransactionPrompt(${t.id}, '${t.type}')">Delete</button>
                        </td>
                    </tr>
                `;
            });
            if (!transactionsBody.innerHTML) transactionsBody.innerHTML = '<tr><td colspan="13">No transactions found</td></tr>';
        }

        async function updateStatistics() {
            const collections = await fetchCollections();
            document.getElementById('totalCollections').textContent = collections.length;

            const sizeTotals = {};
            sizes.forEach(size => sizeTotals[size] = 0);
            let totalItems = 0;
            collections.forEach(c => {
                sizes.forEach(s => {
                    const qty = parseInt(c[s]) || 0;
                    sizeTotals[s] += qty;
                    totalItems += qty;
                });
            });
            document.getElementById('totalItems').textContent = totalItems;

            let maxSize = '-', maxQty = 0;
            for (const [size, qty] of Object.entries(sizeTotals)) {
                if (qty > maxQty) { maxSize = size; maxQty = qty; }
            }
            document.getElementById('popularSize').textContent = maxSize;

            document.getElementById('lastUpdated').textContent = collections.reduce((max, c) => c.last_updated > max ? c.last_updated : max, '-') || '-';

            const sizeDistributionBody = document.getElementById('sizeDistributionBody');
            sizeDistributionBody.innerHTML = sizes.map(size => `
                <tr>
                    <td>${size}</td>
                    <td>${sizeTotals[size]}</td>
                    <td>${totalItems > 0 ? ((sizeTotals[size] / totalItems) * 100).toFixed(1) + '%' : '0.0%'}</td>
                </tr>
            `).join('');
        }

        // Delete Functions
        async function deleteCollectionPrompt(name) {
            if (!confirm(`Delete collection ${name}?`)) return;
            try {
                await deleteCollection(name);
                showNotification(`Deleted ${name}`);
                await updateAll();
            } catch (e) {
                showNotification(`Error deleting collection: ${e.message}`, 'error');
            }
        }

        async function deleteTransactionPrompt(id, type) {
            if (!confirm(`Delete this ${type}?`)) return;
            try {
                const transactions = await fetchTransactions();
                const transaction = transactions.find(t => t.id == id);
                if (!transaction) return showNotification('Transaction not found', 'error');

                const collections = await fetchCollections();
                const collection = collections.find(c => c.name === transaction.collection_name);
                if (collection) {
                    const updatedQuantities = {};
                    sizes.forEach(size => updatedQuantities[size] = (collection[size] || 0) + (type === 'issue' ? transaction[size] : -transaction[size] || 0));
                    await updateCollection(collection.name, { quantities: updatedQuantities, last_updated: new Date().toISOString().split('T')[0], notes: transaction.notes });
                }

                await deleteTransaction(id);
                showNotification(`${type.charAt(0).toUpperCase() + type.slice(1)} deleted`);
                await updateAll();
            } catch (e) {
                showNotification(`Error deleting transaction: ${e.message}`, 'error');
            }
        }

        // Reset Functions
        function resetForm() {
            document.getElementById('collection').value = '';
            document.getElementById('entryNotes').value = '';
            sizes.forEach(s => document.getElementById(s).value = 0);
            document.getElementById('entryDate').value = new Date().toISOString().split('T')[0];
        }

        function resetIssueForm() {
            document.getElementById('issueCollection').value = '';
            document.getElementById('issueTo').value = '';
            document.getElementById('issueNotes').value = '';
            sizes.forEach(s => document.getElementById(`issue${s}`).value = 0);
            document.getElementById('issueDate').value = new Date().toISOString().split('T')[0];
        }

        function resetTransactionFilter() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('startDate').value = today;
            document.getElementById('endDate').value = today;
            updateTransactions();
        }

        function exportToExcel() {
            const table = document.getElementById('reportTable');
            let csv = Array.from(table.rows).map(row => Array.from(row.cells).map(cell => `"${cell.textContent}"`).join(',')).join('\n');
            const link = document.createElement('a');
            link.href = 'data:text/csv;charset=utf-8,' + encodeURI(csv);
            link.download = 'inventory_report.csv';
            link.click();
        }

        // Backup/Restore Functions
        async function backupDatabase() {
            try {
                const response = await fetch('backup_restore.php?action=backup');
                const text = await response.text();
                console.log('Backup response:', text);
                const result = JSON.parse(text);
                if (!result.success) throw new Error(result.error);
                const link = document.createElement('a');
                link.href = 'data:text/sql;charset=utf-8,' + encodeURI(result.sql);
                link.download = `inventory_backup_${new Date().toISOString().split('T')[0]}.sql`;
                link.click();
                document.getElementById('backupStatus').textContent = 'Backup successful!';
            } catch (e) {
                document.getElementById('backupStatus').textContent = `Backup failed: ${e.message}`;
            }
        }

        async function restoreDatabase() {
            const fileInput = document.getElementById('restoreFile');
            const file = fileInput.files[0];
            if (!file) return document.getElementById('backupStatus').textContent = 'Please select a file';

            const reader = new FileReader();
            reader.onload = async (e) => {
                const sql = e.target.result;
                try {
                    const result = await apiRequest('backup_restore.php?action=restore', 'POST', { sql });
                    if (!result.success) throw new Error(result.error);
                    document.getElementById('backupStatus').textContent = 'Restore successful! Refreshing...';
                    await updateAll();
                } catch (e) {
                    document.getElementById('backupStatus').textContent = `Restore failed: ${e.message}`;
                }
            };
            reader.readAsText(file);
        }
    </script>
</body>
</html>