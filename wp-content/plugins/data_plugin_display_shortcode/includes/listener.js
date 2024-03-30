jQuery(document).ready(function ($) {

    var connected = false;
    var socket = io.connect('https://webservice-0-2.onrender.com/ccdev', {
        transports: ['websocket']
    });
    socket.on('data_inserted', function (data) {
        processData(data);
    });

    function processData(data) {
        $.ajax({
            url: socket_listener_ajax_object.ajaxurl, // WordPress AJAX handler URL
            type: 'POST',
            data: {
                action: 'insert_socket_data', // AJAX action name for processing data
                sysUUID: data.sysUUID,
                lastrowid: data.lastrowid,
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
                console.log('Data processed successfully:', response);
            },
            error: function (xhr, status, error) {
                console.error('Error processing data:', error);
            }
        });
    }

    socket.on('connect', function () {
        connected = true;
        alert('Connected to the Socket.IO server', connected);
    });
    socket.on('disconnect', function () {
        connected = false;
        alert('Disconnected from the Socket.IO server', connected);
    });
});
