<html>
    <head>
        <script src="../../jquery-3.4.1.js"></script>
        <script src="../../MyDomCreator.js?v=<?=time();?>"></script>
        <link rel="stylesheet" href="../../MyDomStyle.css?v=<?=time();?>">
        <link rel="stylesheet" href="BrowsePage.css?v=<?=time();?>">
    </head>
    <body>
        <div id="BrowsePage_Control"></div>
        <div id="BrowsePage_UserAccount"></div>
        <div id="BrowsePage_Display"></div>
        <div id="BrowsePage_Bottom"></div>
        <script>
            var BrowsePage_Variable = {
                tableSelect : {},
                theTable : {},
                disableCurtain : {},
                inputBox : {},
                alertBox : {},
                updateButton : {},
                userAccountEdit : {},
                //userAccount : {},
                init_table : function() {
                    var aTable = TableCreator.create_table('StripeTable');
                    BrowsePage_Variable.theTable = aTable;
                    $('#BrowsePage_Display').append(BrowsePage_Variable.theTable);
                },
                init_table_select : function() {
                    var container = $('<div />');
                    var label = $('<label />', {text: "Select a table: "});
                    container.append(label);
                    var theSelect = SelectOptionCreator.create_select_option();
                    theSelect.on('change', function(){
                        console.log(this.value);
                        BrowsePage_Ajax.get_table_content(this.value);
                    });
                    container.append(theSelect);
                    BrowsePage_Ajax.get_table_names(theSelect);
                    BrowsePage_Variable.tableSelect = container;
                    $('#BrowsePage_Control').append(BrowsePage_Variable.tableSelect);
                },
                init_disable_curtain : function() {
                    var theCurtain = $('<div />', {class: "DisableCurtain Z50"});
                    BrowsePage_Variable.disableCurtain = theCurtain;
                    $("body").append(BrowsePage_Variable.disableCurtain);
                },
                init_alert_box : function() {
                    var theBox = $('<div />', {class: 'Centered Z100 AlertBox'});
                    var messageArea = $('<div />', {class: 'MessageArea'});
                    var button = $('<button />', {class: 'RelativeHorizontalCentered'});
                    button.text('OK');
                    button.click(function(){
                        BrowsePage_Variable.alertBox.hide();
                    });
                    theBox.append(messageArea);
                    theBox.append(button);
                    BrowsePage_Variable.alertBox = theBox;
                    BrowsePage_Variable.alertBox.show_message = function(newMessage) {
                        BrowsePage_Variable.alertBox.find(".MessageArea").text(newMessage);
                        BrowsePage_Variable.alertBox.show();
                    };
                    BrowsePage_Variable.alertBox.hide();
                    $("body").append(BrowsePage_Variable.alertBox);
                },
                init_update_button : function() {
                    var updateButton = $('<button />', {text:'Execute Update'});
                    updateButton.on('click', function(){
                        var allChanged = BrowsePage_Variable.theTable.find(".ChangedEntry");
                        var updateData = {};
                        var tableName;
                        //var updateData.RowId = {};
                        for(let key in allChanged) {
                            if(allChanged.hasOwnProperty(key) && allChanged[key] instanceof HTMLInputElement) {
                                var parent = $(allChanged[key]).data("Parent");
                                tableName = parent.data("Table");
                                var rowid = parent.data("Rowid");
                                var column = $(allChanged[key]).data("Column"); 
                                var value = $(allChanged[key]).val();
                                //updateData.Table = parent.data("Table");
                                if(updateData[rowid] == undefined) {
                                    updateData[rowid] = {};
                                }
                                updateData[rowid][column] = value;
                            }
                        }
                        BrowsePage_Method.update_table_content(tableName, updateData);
                    });
                    BrowsePage_Variable.updateButton = updateButton;
                    $('#BrowsePage_Bottom').append(BrowsePage_Variable.updateButton);
                },
                init_user_account_div : function() {
                    var container = $('<div />', {class: 'UserAccount'});
                    var editUserAccount = $('<span />', {text: 'Edit Account', class: 'UserAccountDiv'});
                    editUserAccount.on('click', function(){
                        //BrowsePage_Variable.alertBox.show_message('Tala!');
                        BrowsePage_Variable.userAccountEdit.appear();
                    });
                    container.append(editUserAccount);
                    var logout = $('<span />', {text: 'Logout', class: 'UserAccountDiv'});
                    logout.on('click', function(){
                        BrowsePage_Ajax.logout();
                    });
                    container.append(logout);
                    $('body').append(container);
                },
                init_edit_account_div : function() {
                    BrowsePage_Variable.userAccountEdit = $('<div />', {class: 'Centered Z100'});
                    BrowsePage_Variable.userAccountEdit.appendTo($('body'));
                    BrowsePage_Variable.userAccountEdit.appear = function() {
                        BrowsePage_Variable.userAccountEdit.show();
                        BrowsePage_Variable.disableCurtain.show();
                    }
                    //editUsername
                    var editUsernameContainer = $('<div />');
                    var editUsernameTable = TableCreator.create_table();
                    var newUsernameRow = TableCreator.create_table_row();
                    newUsernameRow.add_entry('New Username');
                    var newUsernameInput = $('<input />', {type: 'text'});
                    newUsernameInput.change(function() {
                        if(newUsernameInput.val().length > 0) {
                            updateUsernameButton.prop('disabled', false);
                        }
                        else {
                            updateUsernameButton.prop('disabled', true);
                        }
                    });
                    newUsernameRow.add_entry(newUsernameInput);
                    editUsernameTable.add_content_row(newUsernameRow);
                    var editUsernamePasswordRow = TableCreator.create_table_row();
                    editUsernamePasswordRow.add_entry('Password');
                    var editUsernamePasswordInput = $('<input />', {type: 'password'});
                    editUsernamePasswordRow.add_entry(editUsernamePasswordInput);
                    editUsernameTable.add_content_row(editUsernamePasswordRow);
                    editUsernameContainer.append(editUsernameTable);
                    var updateUsernameButton = $('<button />', {text:'Update'});
                    updateUsernameButton.click(function() {
                        BrowsePage_Ajax.update_username(newUsernameInput.val(), editUsernamePasswordInput.val());
                        BrowsePage_Variable.userAccountEdit.hide();
                        BrowsePage_Variable.disableCurtain.hide();
                    });
                    editUsernameContainer.append(updateUsernameButton);
                    BrowsePage_Variable.userAccountEdit.append(editUsernameContainer);
                    //editPassword
                    var editPasswordContainer = $('<div />');
                    var editPasswordTable = TableCreator.create_table();
                    var newPasswordRow = TableCreator.create_table_row();
                    newPasswordRow.add_entry('New Password');
                    var newPasswordInput = $('<input />', {type: 'password'});
                    newPasswordInput.change(function() {
                        var newPasswordValue = newPasswordInput.val();
                        var repeatPasswordValue = repeatPasswordInput.val();
                        if(newPasswordValue.length > 0 && (newPasswordValue == repeatPasswordValue)) {
                            updatePasswordButton.prop('disabled', false);
                            repeatPasswordInput.css("background-color", "#FFFFFF");
                        }
                        else {
                            updatePasswordButton.prop('disabled', true);
                            repeatPasswordInput.css("background-color", "#CC6666");
                        }
                    });
                    newPasswordRow.add_entry(newPasswordInput);
                    editPasswordTable.add_content_row(newPasswordRow);
                    var repeatPasswordRow = TableCreator.create_table_row();
                    repeatPasswordRow.add_entry('Repeat Password');
                    var repeatPasswordInput = $('<input />', {type: 'password'});
                    repeatPasswordInput.change(function() {
                        var newPasswordValue = newPasswordInput.val();
                        var repeatPasswordValue = repeatPasswordInput.val();
                        if(newPasswordValue.length > 0 && (newPasswordValue == repeatPasswordValue)) {
                            updatePasswordButton.prop('disabled', false);
                            repeatPasswordInput.css("background-color", "#FFFFFF");
                        }
                        else {
                            updatePasswordButton.prop('disabled', true);
                            repeatPasswordInput.css("background-color", "#CC6666");
                        }
                    });
                    repeatPasswordRow.add_entry(repeatPasswordInput);
                    editPasswordTable.add_content_row(repeatPasswordRow);
                    var oldPasswordRow = TableCreator.create_table_row();
                    oldPasswordRow.add_entry('Old Password');
                    var oldPasswordInput = $('<input />', {type: 'password'});
                    oldPasswordRow.add_entry(oldPasswordInput);
                    editPasswordTable.add_content_row(oldPasswordRow);
                    editPasswordContainer.append(editPasswordTable);
                    var updatePasswordButton = $('<button />', {text:'Update'});
                    updatePasswordButton.prop("disabled", true);
                    updatePasswordButton.click(function() {
                        BrowsePage_Ajax.update_password(newPasswordInput.val(), oldPasswordInput.val());
                        BrowsePage_Variable.userAccountEdit.hide();
                        BrowsePage_Variable.disableCurtain.hide();
                    });
                    editPasswordContainer.append(updatePasswordButton);
                    BrowsePage_Variable.userAccountEdit.append(editPasswordContainer);
                    editPasswordContainer.hide();
                    //Radio button
                    var radioContainer = $('<div />');
                    BrowsePage_Variable.userAccountEdit.prepend(radioContainer);
                    var radioUsername = $('<input type="radio" name="username_password" value="username" checked/>');
                    radioUsername.click(function() {
                        editUsernameContainer.show();
                        editPasswordContainer.hide();
                    });
                    radioContainer.append(radioUsername);
                    radioContainer.append('useranme');
                    var radioPassword = $('<input type="radio" name="username_password" value="password"/>');
                    radioPassword.click(function() {
                        editUsernameContainer.hide();
                        editPasswordContainer.show();
                    });
                    radioContainer.append(radioPassword);
                    radioContainer.append('password');

                    var cancelButton = $('<button />', {text: 'Cancel'});
                    cancelButton.on('click', function(){
                        BrowsePage_Variable.userAccountEdit.hide();
                        BrowsePage_Variable.disableCurtain.hide();
                    });
                    BrowsePage_Variable.userAccountEdit.append(cancelButton);

                    $('body').append(BrowsePage_Variable.userAccountEdit);
                    BrowsePage_Variable.userAccountEdit.hide();
                }
            };
            var BrowsePage_Method = {
                create_data_row : function(tableName, columnName, rowData) {
                    //1st element of rowData is always rowid
                    var dataRow = TableCreator.create_table_row();
                    dataRow.data("Table", tableName);
                    dataRow.data("Rowid", rowData[0]);
                    for(var i=1; i<rowData.length; ++i) {
                        var inputEntry = $('<input />', {type:'text'});
                        inputEntry.val(rowData[i]);
                        inputEntry.data("Ori", rowData[i]);
                        inputEntry.data("Parent", dataRow);
                        inputEntry.data("Column", columnName[i]);
                        inputEntry.on('change', function(){
                            //console.log('changed');
                            var ori = $(this).data("Ori");
                            if($(this).val() === ori) {
                                $(this).removeClass("ChangedEntry");
                            }
                            else {
                                $(this).addClass("ChangedEntry");
                            }
                        });
                        dataRow.add_entry(inputEntry);
                    }
                    var deleteButton = $('<button />', {text: "Remove"});
                    deleteButton.data("Parent", dataRow);
                    deleteButton.on('click', function(){
                        var parent = $(this).data("Parent");
                        var theTable = parent.data("Table");
                        var theRowid = parent.data("Rowid");
                        BrowsePage_Ajax.delete_row(theTable, theRowid);
                    });
                    dataRow.add_entry(deleteButton);
                    return dataRow;
                },
                create_insert_row : function(tableName) {
                    var insertRow = TableCreator.create_table_row();
                    insertRow.data("Table", tableName);
                    insertRow.add_column = function(columnName) {
                        var newColumn = $('<input />', {type:'text'});
                        newColumn.data("ColumnName", columnName);
                        this.add_entry(newColumn);
                    }
                    insertRow.add_insert_button = function() {
                        var insertButton = $('<button />', {text: "Insert"});
                        insertButton.data("Parent", this);
                        insertButton.on('click', function(){
                            var parent = $(this).data("Parent");
                            var theInputs = parent.find('td input');
                            var valuePairList = [];
                            for(let key in theInputs) {
                                if(theInputs.hasOwnProperty(key) && theInputs[key] instanceof HTMLInputElement) {
                                    var valuePair = {};
                                    valuePair["Column"] = $(theInputs[key]).data("ColumnName");
                                    valuePair["Value"] = $(theInputs[key]).val();
                                    if(valuePair["Value"].length > 0) {
                                        valuePairList.push(valuePair);
                                    }
                                    //console.log("key " + $(theInputs[key]).data("ColumnName") + " Value " + $(theInputs[key]).val());
                                }
                            }
                            BrowsePage_Ajax.insert_new_row(parent.data("Table"), valuePairList);
                        });
                        this.add_entry(insertButton);
                    }
                    return insertRow;
                },
                update_table_content : function(tableName, theDataMap) {
                    var toPost = {};
                    toPost.Command = "Update";
                    updateStringArray = [];
                    //console.log("Table: " + tableName);
                    for(let rowid in theDataMap) {
                        //console.log("RowId: " + rowid);
                        var updateString = "Update " + tableName + " Set ";
                        for(let column in theDataMap[rowid]) {
                            //console.log(column + " : " + theDataMap[rowid][column]);
                            updateString += column + "='" + theDataMap[rowid][column] + "',";
                        }
                        updateString = updateString.substring(0, updateString.length-1);
                        updateString += " where rowid==" + rowid;
                        updateStringArray.push(updateString);
                        //console.log(updateString);
                    }
                    BrowsePage_Ajax.execute_multiple_update(tableName, updateStringArray, 0, 0, 0);
                }
            };
            var BrowsePage_Ajax = {
                get_table_content : function(tableName) {
                    var toPost = {};
                    toPost.Command = "Query";
                    toPost.Statement = "Select rowid as rowid, * from " + tableName;
                    $.ajax({
                        type: "POST",
                        url: 'BackendQuery.php',
                        data: "postData=" + JSON.stringify(toPost),
                        success: function(data) {
                            var reply = JSON.parse(data);
                            console.log(reply);
                            if(reply.Status == "Good") {
                                //redirect
                                if(reply.Data != undefined) {
                                    BrowsePage_Variable.theTable.clear();
                                    var labelArray = reply.Data.Label;
                                    if(labelArray != undefined) {
                                        var labelRow = TableCreator.create_table_row();
                                        for(var i=1; i<labelArray.length; ++i) {
                                            labelRow.add_entry(labelArray[i]);
                                        }
                                        labelRow.add_entry("");
                                        BrowsePage_Variable.theTable.set_label_row(labelRow);
                                        var allContent = reply.Data.Value;
                                        for(var i=0; i<allContent.length; ++i) {
                                            var dataRow = BrowsePage_Method.create_data_row(tableName, labelArray, allContent[i]);
                                            BrowsePage_Variable.theTable.add_content_row(dataRow);
                                        }
                                        var insertRow = BrowsePage_Method.create_insert_row(tableName);
                                        for(var i=1; i<labelArray.length; ++i) {
                                            insertRow.add_column(labelArray[i]);
                                        }
                                        insertRow.add_insert_button();
                                        BrowsePage_Variable.theTable.add_content_row(insertRow);
                                    }
                                    else {
                                        BrowsePage_Ajax.get_table_label(tableName);
                                    }
                                }
                            }
                            else if(reply.message == "Prohibited") {
                                window.location = "Login.php";
                            }
                            else {
                                BrowsePage_Variable.alertBox.show_message("Query error");
                            }
                        },
                        error: function(data) {
                            BrowsePage_Variable.alertBox.show_message("Query no reply");
                        }
                    });
                },
                get_table_label : function(tableName) {
                    var toPost = {};
                    toPost.Command = "Query";
                    toPost.Statement = "Select name from pragma_table_info('" + tableName + "')";
                    $.ajax({
                        type: "POST",
                        url: 'BackendQuery.php',
                        data: "postData=" + JSON.stringify(toPost),
                        success: function(data) {
                            var reply = JSON.parse(data);
                            console.log(reply);
                            if(reply.Status == "Good") {
                                //redirect
                                if(reply.Data != undefined) {
                                    BrowsePage_Variable.theTable.clear();
                                    var allContent = reply.Data.Value;
                                    var labelRow = TableCreator.create_table_row();
                                    for(var i=0; i<allContent.length; ++i) {
                                        labelRow.add_entry(allContent[i][0]);
                                    }
                                    labelRow.add_entry("");
                                    BrowsePage_Variable.theTable.set_label_row(labelRow);
                                    var insertRow = BrowsePage_Method.create_insert_row(tableName);
                                    for(var i=0; i<allContent.length; ++i) {
                                        insertRow.add_column(allContent[i][0]);
                                    }
                                    insertRow.add_insert_button();
                                    BrowsePage_Variable.theTable.add_content_row(insertRow);
                                }
                            }
                            else if(reply.message == "Prohibited") {
                                window.location = "Login.php";
                            }
                            else {
                                BrowsePage_Variable.alertBox.show_message("Query error");
                            }
                        },
                        error: function(data) {
                            BrowsePage_Variable.alertBox.show_message("Query no reply");
                        }
                    });
                },
                execute_multiple_update : function(tableName, statementArray, index, successCount, errorCount) {
                    var toPost = {};
                    toPost.Command = "Update";
                    toPost.Statement = statementArray[index];
                    $.ajax({
                        type: 'POST',
                        url: 'BackendQuery.php',
                        data: "postData=" + JSON.stringify(toPost),
                        success: function(data) {
                            var reply = JSON.parse(data);
                            console.log(reply);
                            if(reply.Status == "Good") {
                                ++successCount;
                            }
                            else {
                                console.log(reply);
                                ++errorCount;
                            }
                            if(index+1 < statementArray.length) {
                                BrowsePage_Ajax.execute_multiple_update(tableName, statementArray, index+1, successCount, errorCount);
                            }
                            else {
                                BrowsePage_Variable.alertBox.show_message("Total update: " + successCount + " Total error: " + errorCount);
                                BrowsePage_Ajax.get_table_content(tableName);
                            }
                        },
                        error: function(data) {
                            BrowsePage_Variable.alertBox.show_message("Query no reply, Total update: " + successCount + " Total error: " + errorCount + " Unexecuted: " + statementArray.length-successCount-errorCount);
                        }
                    });                  
                },
                insert_new_row : function(tableName, columnValuePair) {
                    var toPost = {};
                    toPost.Command = "Update";
                    toPost.Statement = "Insert into "+ tableName;
                    if(columnValuePair.length > 0) {
                        var columnString ="(";
                        var valueString="(";
                        columnString += columnValuePair[0].Column;
                        valueString += "'" + columnValuePair[0].Value + "'";
                        for(var i=1; i<columnValuePair.length; ++i) {
                            columnString += "," + columnValuePair[i].Column;
                            valueString += "," + "'" + columnValuePair[i].Value + "'";
                        }
                        columnString += ")";
                        valueString += ")";
                        toPost.Statement += " " + columnString + "Values" + valueString;
                    }
                    else {
                        toPost.Statement += " DEFAULT VALUES";
                    }
                    $.ajax({
                        type: "POST",
                        url: 'BackendQuery.php',
                        data: "postData=" + JSON.stringify(toPost),
                        success: function(data) {
                            //console.log(data);
                            var reply = JSON.parse(data);
                            if(reply.Status == "Good") {
                                BrowsePage_Ajax.get_table_content(tableName);
                                //console.log("Successfull")
                            }
                            else if(reply.message == "Prohibited") {
                                window.location = "Login.php";
                            }
                            else {
                                BrowsePage_Variable.alertBox.show_message("Insert Failed: " + reply.Message);
                                //console.log("Failed")
                            }
                        },
                        error: function(data) {
                            BrowsePage_Variable.alertBox.show_message("Insert operation timeout");
                            //console.log("Error")
                        }
                    });
                },
                delete_row : function(tableName, rowid) {
                    var toPost = {};
                    toPost.Command = "Update";
                    toPost.Statement = "Delete from " + tableName + " where rowid==" + rowid;
                    $.ajax({
                        type: 'POST',
                        url: 'BackendQuery.php',
                        data: 'postData=' + JSON.stringify(toPost),
                        success: function(data) {
                            var reply = JSON.parse(data);
                            if(reply.Status == 'Good') {
                                BrowsePage_Ajax.get_table_content(tableName);
                            }
                            else if(reply.message == "Prohibited") {
                                window.location = "Login.php";
                            }
                            else {
                                BrowsePage_Variable.alertBox.show_message("Delete Failed: " + reply.Message);
                            }
                        },
                        error: function(data) {
                            BrowsePage_Variable.alertBox.show_message("Delete operation timeout");
                        }
                    });
                },
                get_table_names : function(theList) {
                    var toPost = {};
                    toPost.Command = "Query";
                    toPost.Statement = "Select name from sqlite_master WHERE type='table'";
                    $.ajax({
                        type: "POST",
                        url: 'BackendQuery.php',
                        data: "postData=" + JSON.stringify(toPost),
                        success: function(data) {
                            var reply = JSON.parse(data);
                            if(reply.Status == "Good") {
                                if(reply.Data != undefined) {
                                    if(reply.Data.Value != undefined) {
                                        theList.remove_all_option();
                                        var Value = reply.Data.Value;
                                        for(var i=0; i<Value.length; ++i) {
                                            theList.add_option(Value[i][0], Value[i][0]);
                                        }
                                    }
                                }
                            }
                            else if(reply.message == "Prohibited") {
                                window.location = "Login.php";
                            }
                        },
                        error: function(data) {
                            BrowsePage_Variable.alertBox.show_message("Get table name timeout");
                        }
                    });
                },
                logout : function() {
                    var toPost = {};
                    toPost.Command = "Logout";
                    $.ajax({
                        type: 'POST',
                        url: 'BackendLogin.php',
                        data: 'postData=' + JSON.stringify(toPost),
                        success: function(data) {
                            window.location = "Login.php";
                        },
                        error: function(data) {

                        }
                    });
                },
                update_username : function(newUsername, password) {
                    //console.log("Going to update username, newUsername " + newUsername + " password " + password);
                    var toPost = {};
                    toPost.Command = "ChangeUsername";
                    toPost.Username = newUsername;
                    toPost.Password = password;
                    $.ajax({
                        type: "POST",
                        url: 'BackendLogin.php',
                        data: "postData=" + JSON.stringify(toPost),
                        success: function(data) {
                            var reply = JSON.parse(data);
                            if(reply.Status == "Good") {
                                BrowsePage_Variable.alertBox.show_message("Username updated");
                            }
                            else {
                                BrowsePage_Variable.alertBox.show_message("Update username failed");
                            }
                        },
                        error: function(data) {
                            BrowsePage_Variable.alertBox.show_message("Update username timeout");
                        }
                    });
                },
                update_password : function(newPassword, oldPassword) {
                    //console.log("Going to update password, newPassword " + newPassword + " oldePassword " + oldPassword);
                    var toPost = {};
                    toPost.Command = "ChangePassword";
                    toPost.Password = oldPassword;
                    toPost.NewPassword = newPassword;
                    $.ajax({
                        type: "POST",
                        url: 'BackendLogin.php',
                        data: "postData=" + JSON.stringify(toPost),
                        success: function(data) {
                            var reply = JSON.parse(data);
                            if(reply.Status == "Good") {
                                BrowsePage_Variable.alertBox.show_message("Password updated");
                            }
                            else {
                                BrowsePage_Variable.alertBox.show_message("Update password failed");
                            }
                        },
                        error: function(data) {
                            BrowsePage_Variable.alertBox.show_message("Update password timeout");
                        }
                    });
                }
            };

            $(document).ready(function() {
                BrowsePage_Variable.init_table_select();
                BrowsePage_Variable.init_table();
                BrowsePage_Variable.init_disable_curtain();
                BrowsePage_Variable.init_alert_box();
                BrowsePage_Variable.init_update_button();
                BrowsePage_Variable.init_user_account_div();
                BrowsePage_Variable.init_edit_account_div();
            });
        </script>
    </body>
</html>