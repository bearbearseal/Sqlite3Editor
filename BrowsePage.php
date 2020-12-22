<html>
    <head>
        <script src="../../jquery-3.4.1.js"></script>
        <script src="../../MyDomCreator.js?v=<?=time();?>"></script>
        <link rel="stylesheet" href="../../MyDomStyle.css?v=<?=time();?>">
        <link rel="stylesheet" href="BrowsePage.css?v=<?=time();?>">
    </head>
    <body>
        <div id="BrowsePage_Control"></div>
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
                        console.log('clicked');
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
                            else {
                                console.log(reply);
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
                            console.log(data);
                            var reply = JSON.parse(data);
                            if(reply.Status == "Good") {
                                //redirect
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
                            else {
                            }
                        },
                        error: function(data) {
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
                //BrowsePage_Variable.init_disable_curtain();
                //BrowsePage_Variable.init_alert_box();
/*
                var aTable = TableCreator.create_table('StripeTable');
                var aRow = TableCreator.create_table_row();
                aRow.add_entry("one");
                aRow.add_entry("two");
                aRow.add_entry("three");
                aTable.add_label_row(aRow);
                var contentRow = TableCreator.create_table_row();
                contentRow.add_entry($('<button />', {text: "fsdfsd"}));
                contentRow.add_entry($('<input />', {type: "text"}));
                contentRow.add_entry("dsdfsf");
                aTable.add_content_row(contentRow);
                var contentRow2 = TableCreator.create_table_row();
                contentRow2.add_entry($('<button />', {text: "fsdfsd"}));
                contentRow2.add_entry($('<input />', {type: "text"}));
                contentRow2.add_entry("dsdfsf");
                aTable.add_content_row(contentRow2);
                $("#BrowsePage_Display").append(aTable);
*/
            });
        </script>
    </body>
</html>