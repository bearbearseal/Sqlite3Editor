<?php
   session_start();
?>

<html>
    <head>
        <script src="../../jquery-3.4.1.js"></script>
        <script src="../../MyDomCreator.js?v=<?=time();?>"></script>
        <link rel="stylesheet" href="../../MyDomStyle.css?v=<?=time();?>">
    </head>
    <body>
        <script>
            var LoginPage = {
                feedback : {},
                init : function() {
                    var theTable = TableCreator.create_table('Centered');
                    var usernameRow = TableCreator.create_table_row();
                    usernameRow.add_entry("Username");
                    usernameInput = $('<input />', {type: 'text'});
                    usernameRow.add_entry(usernameInput);
                    theTable.add_content_row(usernameRow);
                    var passwordRow = TableCreator.create_table_row();
                    passwordRow.add_entry("Password");
                    var passwordInput = $('<input />', {type: 'password'});
                    passwordRow.add_entry(passwordInput);
                    theTable.add_content_row(passwordRow);
                    var buttonRow = TableCreator.create_table_row();
                    var submitButton = $('<button />', {text: 'Login'});
                    submitButton.click(function(){
                        console.log("Clicked");
                        LoginPage.send_login(usernameInput.val(), passwordInput.val());
                    });
                    LoginPage.feedback = $('<span />', {text: 'please login'});
                    buttonRow.add_entry(submitButton);
                    buttonRow.add_entry(LoginPage.feedback);
                    theTable.add_content_row(buttonRow);
                    $('body').append(theTable);
                },
                set_feedback : function(message) {
                    LoginPage.feedback.text(message);
                },
                send_login : function(username, password) {
                    var toPost = {};
                    toPost.Command = "Login";
                    toPost.Username = username;
                    toPost.Password = password;
                    //console.log(username + " " + password);
                    $.ajax({
                        type: "POST",
                        url: 'BackendLogin.php',
                        data: "postData=" + JSON.stringify(toPost),
                        success: function(data) {
                            var reply = JSON.parse(data);
                            console.log(reply);
                            if(reply.status == "Good") {
                                LoginPage.set_feedback('');
                                window.location = "BrowsePage.php";
                            }
                            else {
                                LoginPage.set_feedback('Login failed');
                            }
                        },
                        error: function(data) {
                            LoginPage.set_feedback('Server did not respond');
                        }
                    });
                }
            }
            $(document).ready(function() {
                LoginPage.init();
            });
        </script>
    </body>
</html>