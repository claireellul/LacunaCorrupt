		function uploadEdits() {
			console.log(vertexModel)
			aPoint = (vertexMesh.hasOwnProperty("geometry") && vertexMesh.geometry instanceof THREE.SphereGeometry && vertexMesh.name != "Helper" )
			aObject3D = ( vertexObject instanceof THREE.Object3D === true  && vertexObject instanceof THREE.Scene === false && vertexMesh instanceof THREE.Line == false )
			aLine = ( vertexMesh instanceof THREE.Line === true && vertexMesh.parent instanceof THREE.Scene === true  );
			aPolygon = ( vertexMesh instanceof THREE.Mesh && vertexMesh.parent instanceof THREE.Scene === true && vertexMesh.geometry instanceof THREE.SphereGeometry === false && vertexMesh.name != "Helper" )
			name = vertexModel.name.split(" ")[0]
			id = vertexModel.name.split(" ")[1]

			if (aPoint) { console.log("A point is being saved"); geom = point_to_pg(vertexModel) }
			else if (aObject3D) { geom = threejs_to_tin(vertexModel) }
			else if (aLine 	) { geom = line_to_pg(vertexModel) }
			else if (aPolygon) { geom = polygon_to_pg(vertexModel) }


			replaceGeomPG(name, id, geom)
		}


		function discardChanges() {
			console.log (cloneGeometry)
			if (vObject3D) {
				//console.log(vertexObject)
				oObject = new THREE.Object3D
				oObject.name = vertexObject.name
				oColor = vertexObject.children[0].material.color.clone()
				cloneGeometry.forEach( function(m, i) {
					oObject.add(new THREE.Mesh( cloneGeometry[i], new THREE.MeshLambertMaterial( { color: oColor.clone(), ambient: oColor.clone() } )))
				})
				scene.remove(vertexObject)
				scene.add(oObject)
			}
			else if (vPolygon) {
				oMesh = new THREE.Mesh( cloneGeometry[0], new THREE.MeshLambertMaterial( { color: oColor.clone(), ambient: oColor.clone() } ))
				oMesh.name = vertexMesh.name
				scene.remove(vertexMesh)
				scene.add(oMesh)
			}
			else if (vLine) {
				oLine = new THREE.Line( cloneGeometry[0], new THREE.LineBasicMaterial( { color: oColor.clone(), ambient: oColor.clone() } ))
				oLine.name = vertexMesh.name
				scene.remove(vertexMesh)
				scene.add(oLine)
			}

			vLine = false
			vPoint = false
			vPolygon = false
			vObject3D = false

		}
