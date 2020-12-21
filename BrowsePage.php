<html>
    <head>
        <script src="../../jquery-3.4.1.js"></script>
        <script src="../../MyDomCreator.js?v=<?=time();?>"></script>
        <link rel="stylesheet" href="../../MyDomStyle.css?v=<?=time();?>">
    </head>
    <body>
        <div id="BrowsePage_Control"></div>
        <div id="BrowsePage_Display"></div>
        <script>
            var BrowsePage_Variable = {
                tableSelect : {},
                theTable : {},
                disableCurtain : {},
                inputBox : {},
                alertBox : {},
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
                }
            };
            var BrowsePage_Method = {
                create_table_entry : function(text, rowid, columnName) {
                    var theEntry = $('<div />');
                    theEntry.text(text);
                    return theEntry;
                },
                create_data_row : function(tableName, rowData) {
                    //1st element of rowData is always rowid
                    var dataRow = TableCreator.create_table_row();
                    dataRow.data("Table", tableName);
                    dataRow.data("Rowid", rowData[0]);
                    for(var i=1; i<rowData.length; ++i) {
                        dataRow.add_entry(rowData[i]);
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
                                    var labelRow = TableCreator.create_table_row();
                                    for(var i=1; i<labelArray.length; ++i) {
                                        labelRow.add_entry(labelArray[i]);
                                    }
                                    labelRow.add_entry("");
                                    BrowsePage_Variable.theTable.set_label_row(labelRow);
                                    var allContent = reply.Data.Value;
                                    for(var i=0; i<allContent.length; ++i) {
                                        var dataRow = BrowsePage_Method.create_data_row(tableName, allContent[i]);
                                        /*
                                        var contentRow = TableCreator.create_table_row();
                                        for(var j=1; j<allContent[i].length; ++j) {                                            
                                            contentRow.add_entry(allContent[i][j]);
                                        }
                                        //create a delete button
                                        contentRow.add_entry($('<button />', {text:'Delete'}));
                                        BrowsePage_Variable.theTable.add_content_row(contentRow);
                                        */
                                        BrowsePage_Variable.theTable.add_content_row(dataRow);
                                    }
                                    var insertRow = BrowsePage_Method.create_insert_row(tableName);
                                    for(var i=1; i<labelArray.length; ++i) {
                                        insertRow.add_column(labelArray[i]);
                                    }
                                    insertRow.add_insert_button();
                                    BrowsePage_Variable.theTable.add_content_row(insertRow);
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
                update_table_content : function(tableName, rowid, columnName, newValue) {
                    var toPost = {};
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