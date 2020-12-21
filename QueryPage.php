<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="QueryPage.css?v=<?=time();?>">
        <link rel="stylesheet" href="../../bootstrap-4.4.1-dist/css/bootstrap.min.css">
        <script src="../../jquery-3.4.1.js"></script>
        <script src="../../popper-1.16.0/popper.min.js"></script>
        <script src="../../bootstrap-4.4.1-dist/js/bootstrap.min.js"></script>
    </head>  
    <body>
        <div>
            <input id='QueryPage_QueryStatement' type="text" size=64>
            <button id='QueryPage_QueryButton' type="button" onclick='QueryPage.send_execute("Query", $("#QueryPage_QueryStatement").val())'>Query</button>
        </div>
        <div>
            <input id='QueryPage_UpdateStatement' type="text" size=64>
            <button id='QueryPage_UpdateButton' type="button" onclick='QueryPage.send_execute("Update", $("#QueryPage_UpdateStatement").val())'>Update</button>
            <div id='QueryPage_ErrorMessage'></div>
        </div>
        <div id='QueryPage_Display'>
        </div>
        <script>
            var QueryPage = {
                send_execute : function(command, statement) {
                    var toPost = {};
                    toPost.Command = command;
                    toPost.Statement = statement;
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
                                    QueryPage.remove_table();
                                    $('#QueryPage_Display').append(QueryPage.create_table(reply.Data.Label, reply.Data.Value));
                                }
                                //$("#QueryPage_Statement").val('');
                                $("#QueryPage_ErrorMessage").text('');
                                //console.log("Query Success");
                                //console.log(reply);
                            }
                            else {
                                $("#QueryPage_ErrorMessage").text(reply.message);
                            }
                        },
                        error: function(data) {
                        }
                    });
                },
                remove_table : function() {
                    $('#QueryPage_Display').empty();
                },
                create_table : function(label, value) {
                    var aTable = $('<table />');
                    aTable.addClass("table");
                    var tableHead = $('<thead />');
                    tableHead.append(QueryPage.create_row(label));
                    aTable.append(tableHead);
                    var tableBody = $('<tbody />');
                    QueryPage.show_data(tableBody, value);
                    aTable.append(tableBody);
                    return aTable;
                },
                create_row : function(rowData) {
                    var tableRow = $('<tr />');
                    if(Array.isArray(rowData)) {
                        for(var i=0; i<rowData.length; ++i) {
                            var entry = $('<td />');
                            entry.text(rowData[i]);
                            tableRow.append(entry);
                        }
                    }
                    return tableRow;
                },
                show_data : function(theTable, value) {
                    if(Array.isArray(value)) {
                        for(var i=0; i<value.length; ++i) {
                            theTable.append(QueryPage.create_row(value[i]));
                        }
                    }
                }
            }
        </script>
    </body>
</html>