jQuery(document).ready(function ($) {
    // Connect to the Socket.IO server
    var socket = null; // Declare socket variable

    // Function to connect to the Socket.IO server
    function connectSocket() {
        socket = io('https://webservice-0-2.onrender.com'); // Replace with your actual WebSocket URL

        // Flag to track connection status
        var connected = false;

        // Listen for 'connect' event to update connection status
        socket.on('connect', function () {
            connected = true;
            // Display connected message using a shortcode
            $('#socket-status').html('[socket_status]Connected[/socket_status]');
        });

        // Listen for 'disconnect' event to update connection status
        socket.on('disconnect', function () {
            connected = false;
            // Display disconnected message using a shortcode
            $('#socket-status').html('[socket_status]Disconnected[/socket_status]');
        });

        // Listen for events from the server
        socket.on('data_inserted', function (data) {
            // Process the received data
            processData(data);
        });
    }

    // Function to process and send data to the server
    function processData(data) {
        // Send the JSON data to the WordPress backend using AJAX
        $.ajax({
            url: socket_listener_ajax_object.ajaxurl, // WordPress AJAX handler URL
            type: 'POST',
            data: {
                action: 'insert_socket_data', // AJAX action name for processing data
                sysUUID: data.sysUUID,
                sid: data.sid,
                score: data.score,
                timestamp: data.timestamp,
                duration: data.duration,
                ts: data.ts
            },
            dataType: 'json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            },
            success: function (response) {
                // Handle success response
                console.log('Data processed successfully:', response);
                // Display success message or take appropriate action
            },
            error: function (xhr, status, error) {
                // Handle error
                console.error('Error processing data:', error);
                // Display error message or take appropriate action
            }
        });
    }

    // Connect button click event
    $('#connect-button').click(function () {
        if (!socket || socket.disconnected) {
            connectSocket(); // Connect to the Socket.IO server
        }
    });
});
