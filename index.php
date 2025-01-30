<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Downloads Extension Interface</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <!-- FontAwesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Basic Styling -->
    <style>
        :root {
            --size: 8px;
            --size2: 8px;
        }

        body {
            display: flex;
            background: hsl(0 0% 6%);
        }

        .el {
            z-index: 0;
            position: fixed;
            background: conic-gradient(from 180deg at 50% 70%,rgb(255, 255, 255) 0deg,rgb(255, 214, 67) 72.0000010728836deg,rgb(255, 0, 0) 144.0000021457672deg,rgb(0, 98, 255) 216.00000858306885deg,rgb(0, 255, 47) 288.0000042915344deg,rgb(0, 221, 255) 1turn);
            width: 100%;
            height: 100%;
            mask:
                radial-gradient(circle at 50% 50%,rgb(0, 0, 0) 3px, transparent 3px) 50% 50% / var(--size) var(--size2),
                url("https://assets.codepen.io/605876/noise-mask.png") 256px 50% / 256px 256px;
            mask-composite: intersect;
            animation: flicker 20s infinite linear;
        }

        @keyframes flicker {
            to {
                mask-position: 50% 50%, 0 50%;
            }
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            overflow-x: auto; /* Enable horizontal scrolling on small screens */
            font-size: 22px;
            justify-content: center;
        }
        table {
            z-index: 100;
            width: 80vw;
            margin: 20px;
            margin-top: 50px;
            max-width: 1000px;
            border-collapse: collapse;
            table-layout: fixed; /* Ensures equal column width distribution */
            box-shadow: 0 12px 5px -5px rgba(0, 0, 0, 0.4), 0 0 8px rgb(0, 0, 0), 0 0 35px rgba(176, 162, 239, 0.65);
            border-radius: 8px;
            background-color:rgb(134, 131, 148);
        }
        th, td {
            padding: 8px;
            text-align: left;
            white-space: nowrap; /* Prevent line breaks in cells */
            overflow: hidden;
            text-overflow: ellipsis; /* Add "..." at the end of text that overflows */
            text-align: center;
            width: 30px;
            min-width: 30px;
            max-width: 30px;
        }
        th {
            background-color:rgb(47, 45, 56);
            color: #fff;
        }
        td {
            padding: 8px;
            white-space: nowrap;
            background-color:rgb(244, 242, 249);
            color: rgb(47, 45, 56);
            box-shadow: inset 0 0 1px 0px rgb(90, 86, 118);
        }
        tr {
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.4)
        }

        .icon-cell a {
            color: rgb(255, 0, 0);
            padding: 0;
            text-decoration: none;
        }
        .icon-cell a:hover {
            color: rgb(197, 0, 0);
        }
        .download-icon a {
            color: #007bff;
            padding: 0;
            text-decoration: none;
        }
        .download-icon a:hover {
            color: #0056b3;
        }
        .url-cell {
            max-width: 300px; /* Limit the width of the URL column */
            width: max-content;
            text-align: start;
        }

        h1 {
            position: fixed;
            width: 100%;
            height: 100%;
            margin: 0;
            backdrop-filter: blur(2px);
            background: linear-gradient(90deg, rgba(47, 45, 56, 0.24) 0%, rgba(0,0,0,0) 20%, rgba(0,0,0,0) 80%, rgba(47, 45, 56, 0.27) 100%), linear-gradient(90deg, rgba(0, 0, 0, 0.6) 0%, rgba(0,0,0,0) 10%, rgba(0,0,0,0) 90%, rgba(0,0,0,0.6) 100%);
        }

        h2 {
            color: #fff;
        }

        .swipe-away {
            transition: 0.5s;
            transform: translateX(-100vw);
            opacity: 0;
        }

        .extra {
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 200;
            margin: 20px;
            gap: 20px;
            align-items: center;
        }

        .extra i {
            color: rgb(47, 45, 56);
            background-color: #fff;
            border-radius: 100vw;
            font-size: 26px;
            padding: 8px;
            box-shadow: 0 0 5px -5px rgba(0, 0, 0, 0.4), 0 0 8px rgb(0, 0, 0), 0 0 20px rgba(176, 162, 239, 0.65);
            aspect-ratio: 1/1;
            align-items: center;
            text-align: center;
            justify-items: center;
            transition: 0.2s;
            cursor: pointer;
        }

        .remove-field {
            transition: 0.2s;
            cursor: pointer;
        }

        .error {
            color: rgb(222, 71, 71);
        }
        
        #alignRight {
            position: fixed;
            right: 20px;
            /*background-color: rgb(203, 50, 50);
            color: white;*/
        }

        @media (hover: hover) {
            .icon-cell a:hover {
                color: rgb(197, 0, 0);
            }
            .download-icon a {
                color: #007bff;
                padding: 0;
                text-decoration: none;
            }
            .download-icon a:hover {
                color: #0056b3;
            }
            .extra i:hover {
                background-color: rgb(47, 45, 56);
                color: #fff;
            }
            .remove-field:hover {
                background-color: rgb(255, 0, 0);
                color: white;
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            th, td {
                font-size: 18px; /* Reduce font size on small screens */
                padding: 6px;
                width: 24px;
                min-width: 24px;
                max-width: 24px;
            }
            table {
                margin-top: 80px;
                width: 100%;
            }
            h1 {
                background: none;
            }
            .extra {
                flex-direction: row;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="el"></div>
    <h1></h1>
    <div class="extra">
        <i class="fa-solid fa-database" onclick="openPhpMyAdmin()"></i>
        <i class="fa-solid fa-rotate-right" id="reloadAll" onclick="markAllDownloaded(this)"></i>
        <i class="fa-solid fa-circle-down" onclick="downloadAllMissing(this)"></i>
        <i class="fa-solid fa-arrow-right-from-bracket" id="alignRight" onclick="logout()"></i>
    </div>

    <table id="downloads-table">
        <thead>
            <tr data-entry-id="123">
                <th style="border-top-left-radius:8px"><i class="fa-solid fa-trash"></i></th>
                <th class="url-cell">Name(<span id="entry-count">Loading...</span>)</th> <!-- Entry count will be displayed here -->
                <th><i class="fa-solid fa-clipboard"></i></th>
                <th><i class="fa-solid fa-download"></i></th>
                <th><i class="fa-solid fa-link"></i></th>
                <th style="border-top-right-radius:8px"><i class="fa-solid fa-file-arrow-down"></i></th>
            </tr>
        </thead>
        <tbody>
            <!-- Data will be dynamically inserted here -->
        </tbody>
    </table>
    
    <script>
        let fetchInterval;
        let ongoingDownloads = 0; // Track the number of ongoing downloads

        function fetchTotalEntries() {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'count_entries.php', true); // The PHP file to count entries
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Set the count to the entry-count span
                    document.getElementById('entry-count').innerText = xhr.responseText;
                } else {
                    document.getElementById('entry-count').innerText = 'Error';
                }
            };
            xhr.send();
        }

        // Call the fetch function when the page loads
        window.onload = function() {
            fetchTotalEntries();
            fetchDownloads(); // Your existing fetch function to load downloads data
        };

        function fetchDownloads() {
            // Fetch the downloads immediately
            performFetch();

            // Then start the interval for periodic updates
            clearInterval(fetchInterval);
            fetchInterval = setInterval(performFetch, 5000); // Fetch every 5 seconds
        }

        function performFetch() {
            if (ongoingDownloads > 0) return; // Do not fetch while downloads are in progress

            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'fetch_downloads.php', true);
            xhr.onload = function () {
                if (xhr.status === 200) {
                    document.querySelector('tbody').innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }

        function copyToClipboard(url) {
            // Create a temporary input element to copy the URL
            var tempInput = document.createElement('input');
            tempInput.value = url;
            document.body.appendChild(tempInput);
            
            // Select and copy the text
            tempInput.select();
            document.execCommand('copy');
            
            // Remove the temporary input element
            document.body.removeChild(tempInput);
            
            // Optionally, you can show a message or alert to indicate the link was copied
            alert(url + ' copied to clipboard!');
        }

        function markAllDownloaded(button) {
            if (!confirm("Are you sure you want to mark all entries as downloaded and download them?")) {
                return;
            }

            // Add loading spinner to button
            button.classList = "fa-solid fa-spinner fa-spin-pulse";

            // Get all rows from the table
            const rows = document.querySelectorAll('#downloads-table tbody tr');
            if (rows.length === 0) {
                alert("No entries found to mark as downloaded.");
                return;
            }

            // Iterate over each row and trigger markDownloaded for each entry
            rows.forEach((row) => {
                markDownloaded(parseInt(row.querySelector('.download-icon').getAttribute('value').replace("\"","")), row.querySelector('.download-icon')); // Call markDownloaded for each entry
            });

            // Reset the button icon after processing
            button.classList = "fa-solid fa-rotate-right";
        }

        function downloadAllMissing(button) {
    if (!confirm("Are you sure you want to download all missing entries?")) {
        return;
    }

    // Add loading spinner to button
    button.classList = "fa-solid fa-spinner fa-spin-pulse";

    // Get all rows from the table
    const rows = document.querySelectorAll('#downloads-table tbody tr');
    if (rows.length === 0) {
        alert("No entries found to download.");
        button.classList = "fa-solid fa-circle-down"; // Reset button icon
        return;
    }

    let missingDownloads = 0;

    // Iterate over each row
    rows.forEach((row) => {
        const downloadedCell = row.querySelector('td:nth-child(4) i'); // Locate the "downloaded" column icon
        const isDownloaded = downloadedCell.classList.contains('fa-square-check'); // Check if it's marked as downloaded
        const id = row.querySelector('.download-icon').getAttribute('value').replace(/"/g, ''); // Extract the entry ID
        const downloadElement = row.querySelector('.download-icon'); // Get the download element

        if (!isDownloaded && id) {
            missingDownloads++;
            markDownloaded(parseInt(id), downloadElement); // Process only missing downloads
        }
    });

    if (missingDownloads === 0) {
        alert("No missing entries to download.");
    }

    // Reset the button icon after processing
    button.classList = "fa-solid fa-circle-down";
}



        // Updated markDownloaded function for each entry
        function markDownloaded(id, element) {
            if (!id || !element) {
                return;
            }
            // Track ongoing downloads
            ongoingDownloads++;
            if (element) {
                var originalIcon = element.innerHTML; // Save the original icon
                element.innerHTML = '<i class="fa-solid fa-spinner fa-spin-pulse"></i>'; // Set spinner icon
            }

            // Create the request to mark the download
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'mark_downloaded.php?id=' + id, true);
            xhr.responseType = 'blob'; // Expect a binary file response
            xhr.onload = function () {
                if (xhr.status === 200) {
                    element.innerHTML = originalIcon;
                    const downloadTd = element.closest('td');
                    const parentRow = downloadTd.closest('tr');
                    const checkIcon = parentRow.querySelector('.fa-regular.fa-square');
                    checkIcon.className = "fa-solid fa-square-check";

                    // Handle the binary file response (MP3 file)
                    var disposition = xhr.getResponseHeader('Content-Disposition');
                    var filename = 'downloaded.mp3'; // Default filename

                    if (disposition && disposition.indexOf('filename=') !== -1) {
                        var matches = disposition.match(/filename[^;=\n]*=([^;\n]*)/);
                        if (matches.length > 1) {
                            filename = matches[1].trim().replace(/["']/g, '');
                        }
                    }

                    // Create a link to trigger the download
                    var blob = new Blob([xhr.response], { type: 'audio/mpeg' });
                    var link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename;
                    link.click();
                }
                else {
                    element.innerHTML = originalIcon.replace("fa-solid fa-spinner fa-spin-pulse","fa-solid fa-download error");
                    alert('Error downloading file with ID TEST: ' + id);
                }

                ongoingDownloads--; // Decrement the ongoing download counter
            };

            xhr.onerror = function () {
                element.innerHTML = originalIcon.replace("fa-solid fa-spinner fa-spin-pulse","fas fa-download error");
                ongoingDownloads--; // Decrement on error
                alert('Error downloading file with ID TEST: ' + id);
            };

            if (ongoingDownloads == 0) {
                document.getElementById("reloadAll").classList = "fa-solid fa-rotate-right";
            }

            xhr.send();
            if (ongoingDownloads == 0) {
                document.getElementById("reloadAll").classList = "fa-solid fa-rotate-right";
            }
        }

        function openPhpMyAdmin() {
            window.open("http://localhost/phpmyadmin/index.php?route=/database/sql&db=downloads", "_blank");
        }

        function removeFromDatabase(id, rowElement) {
            if (confirm('Are you sure you want to delete this entry?')) {
                clearInterval(fetchInterval); // Stop the fetchDownloads loop
                ongoingDownloads++; // Increment the counter for ongoing downloads

                // Create a new XMLHttpRequest object
                var xhr = new XMLHttpRequest();

                // Prepare the request to the PHP file that will handle the deletion
                xhr.open('POST', 'remove_download.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                // Send the ID of the item to be deleted
                xhr.send('id=' + id);

                // When the request completes
                xhr.onload = function() {
                    ongoingDownloads--; // Decrement the counter even on error
                    if (ongoingDownloads === 0) {
                        // Resume fetchDownloads loop only when all downloads are complete
                        setTimeout(function(){fetchDownloads();}, 500);
                    }
                    if (xhr.status === 200) {
                        // Remove the row from the table without reloading the page
                        rowElement.parentElement.classList.add("swipe-away");
                        setTimeout(function(){rowElement.parentElement.remove();}, 500);
                    } else {
                        // Handle error
                        alert('Error deleting entry: ' + xhr.responseText);
                    }
                };

                xhr.onerror = function() {
                    alert('An error occurred while trying to delete the entry.');
                    ongoingDownloads--; // Decrement the counter even on error
                    if (ongoingDownloads === 0) {
                        // Resume fetchDownloads loop only when all downloads are complete
                        setTimeout(function(){fetchDownloads();}, 500);
                    }
                };
            }
        }

        function toggleDownloaded(id, iconElement) {
            // Toggle the icon classes
            if (iconElement.classList.contains('fa-regular')) {
                iconElement.classList.remove('fa-regular', 'fa-square');
                iconElement.classList.add('fa-solid', 'fa-square-check');
            } else {
                iconElement.classList.remove('fa-solid', 'fa-square-check');
                iconElement.classList.add('fa-regular', 'fa-square');
            }
            fetch('toggle_downloaded.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${encodeURIComponent(id)}`,
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert(`Error: ${data.message}`);
                }
            })
            .catch(error => {
                console.error('Error toggling downloaded state:', error);
                alert('An error occurred while toggling the downloaded state.');
            });
        }

        function logout() {
            window.location.href = "logout.php";
        }


        // Start the fetching process when the page loads
        fetchDownloads();
    </script>
</body>
</html>
