			function getattributes () {
				$( "#attributesholder" ).empty();
				SELECTED.sceneobject.forEach( function(selectedObject) {
					if (selectedObject.hasOwnProperty('material')) {
						selectedObject.material.color.setHex( 0xCCCCCC )
						if (selectedObject.material.hasOwnProperty("ambient")) { selectedObject.material.ambient.setHex ( 0xCCCCCC ) }

					}
					else if (selectedObject.hasOwnProperty('children')) {
						selectedObject.children.forEach( function( highlightmesh ) {
							highlightmesh.material.color.setHex( 0xCCCCCC );
							if (highlightmesh.material.hasOwnProperty("ambient")) { highlightmesh.material.ambient.setHex ( 0xCCCCCC ) }
						});
					}
				});
				tables = [] ;
				objectsToGet = [] ;
				ajaxAttributes = false
				SELECTED.sceneobject.forEach( function(so, selectIndex) {
					if ((so.hasOwnProperty('name')) && ((so.name != "") || (so.name != "Helper"))) {
						objectName = so.name
						objectParts = objectName.split(" "); // Split name into TABLE and ID NUMBER [ 'Bridges', '2' ]
						if (tables.indexOf(objectParts[0]) === -1) {
							tables.push(objectParts[0])
							objectsToGet.push([])
						}
							//console.log(tables.indexOf(objectParts[0]));
						tableNum = tables.indexOf(objectParts[0])
						objectsToGet[tableNum].push(objectParts[1])
					}
				});

				//console.log(tablesAndObjectsToGet)
				console.log( JSON.stringify(objectsToGet) );
				console.log( JSON.stringify(tables) );
				var attributeresponse =
					$.ajax({
						  url: './ajax/getattributes.php',
						  type: 'post',
						  dataType: "json",
						  timeout: 60000,
						  data: {'tables': tables, 'attributesToGet': objectsToGet},
						  async: false,
						  success: function(data) {
							}
						 }).responseJSON;


				//console.log(attributeresponse);

				responseToTable(attributeresponse)
			} //End of get attribute function

				function responseToTable(response) {
					response.forEach( function(tableToDisplay, tableIndex) {
						tableString = "";
						//tableToDisplay[1].unshift("Select")
						// Remove ID, put it back in at the begging
						temporaryColumns = tableToDisplay[1]
						//console.log(temporaryColumns);
						idPos = temporaryColumns.indexOf("ID")
						temporaryColumns.splice(idPos, 1)
						//console.log(temporaryColumns)
						temporaryColumns.unshift("ID")
						columnNames = temporaryColumns
						//console.log(columnNames);
						rows = tableToDisplay[2]

						tableString = tableString.concat("<tr>")

						columnNames.forEach( function(columnHeading) {
							if ((columnHeading != "geom")) {
								tableString = tableString + "<th>"
								tableString = tableString + columnHeading
								tableString = tableString + "</th>"
							}
						});

						//console.log(tableString);
						tableString = tableString.concat("</tr>")

						//tr = new row td = new cell
						// For each row

						rows.forEach( function(attributeRow) {
							tableString = tableString.concat("<tr>")
								//Create all the row cells
								columnNames.forEach( function( colHeader, colIndex) {
									if (colHeader != "geom") {
										tableString = tableString.concat("<td>")
										tableString = tableString.concat(attributeRow[colHeader])
										//console.log(attributeRow[colHeader])
										tableString = tableString.concat("</td>")
									}
								});
							tableString = tableString.concat("</tr>")
						});

						finalString = ["<div>", "<br>",'<table class="attributetables" >', "<caption style='text-align: left;'>", attributeresponse[tableIndex][0],  "</caption>", tableString, "</table>", "</div>", "<br>"].join("")
						$("#attributesholder").append(finalString);
						hoverrows();
					});

				}
