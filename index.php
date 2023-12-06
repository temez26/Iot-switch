<!DOCTYPE html>
<html>

<head>
    <title>Button Control</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function () {
            // Fetch the current state from the server when the page loads
            $.ajax({
                url: 'server.php', // Server URL
                type: 'GET', // Request type
                cache: false, // Disable caching
                success: function (data) {
                    if (data.success) {
                        var value = data.value === 'true'; // Convert the string to a boolean
                        var newValue = value ? "On" : "Off"; // Set the button text based on the value
                        $("#controlButton").text(newValue); // Update the button text
                        $("#controlButton").data('state', value); // Store the state in a data attribute

                        // Set the classes based on the server's state
                        if (value === true) {
                            $("#controlButton").removeClass("off").addClass("on");
                        } else if (value === false) {
                            $("#controlButton").removeClass("on").addClass("off");
                        } else {
                            console.error("Unexpected value for data.value: ", data.value);
                        }
                    } else {
                        alert("Error: " + data.error); // Show an error alert if the request was not successful
                    }
                }
            });

            // Handle the button click event
            $("#controlButton").click(function () {
                var value = $("#controlButton").data('state') ? false : true; // Use the data attribute to get the state
                $.ajax({
                    url: 'server.php', // Server URL
                    type: 'POST', // Request type
                    contentType: 'application/json', // Set the content type to JSON
                    cache: false, // Disable caching
                    data: JSON.stringify({ value: value }), // Send the new value to the server
                    success: function (data) {
                        if (data.success) {
                            var newValue = value ? "On" : "Off"; // Set the button text based on the value
                            $("#controlButton").text(newValue); // Update the button text
                            $("#controlButton").data('state', value); // Update the state in the data attribute

                            // Set the classes based on the new state
                            if (value === true) {
                                $("#controlButton").removeClass("off").addClass("on");
                            } else if (value === false) {
                                $("#controlButton").removeClass("on").addClass("off");
                            } else {
                                console.error("Unexpected value for value: ", value);
                            }
                        } else {
                            alert("Error: " + data.error); // Show an error alert if the request was not successful
                        }
                    }
                });
            });
        });
    </script>
    <style>
        body {
            background-color: lightblue;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        #controlButton {
            width: 200px;
            height: 100px;
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }

        #controlButton.on {
            background-color: green;
        }

        #controlButton.off {
            background-color: red;
        }
    </style>
</head>

<body>
    <button id="controlButton" class="on">On</button>
</body>

</html>
