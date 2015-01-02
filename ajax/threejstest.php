<?php

// first the vertex array
[{"x":529521.376878298,"y":182238.700127146,"z":1.02523780691187},{"x":529525.511197145,"y":182233.04278369,"z":1.0257378069118699},{"x":529525.979479803,"y":182233.384999049,"z":1.0257378069118699},{"x":529522.188326634,"y":182238.572759444,"z":1.0257378069118699}]" lacuna.js:329
[{"x":529521.376878,"y":182238.700127,"z":1.02523780691},{"x":529525.511197,"y":182233.042784,"z":1.02573780691},{"x":529525.97948,"y":182233.384999,"z":1.02573780691},{"x":529522.188327,"y":182238.572759,"z":1.02573780691}]

// then the faces array
[{"a":3,"b":0,"c":1,"normal":{"x":0,"y":0,"z":0},"vertexNormals":[],"color":{},"vertexColors":[],"vertexTangents":[],"materialIndex":0},{"a":1,"b":2,"c":3,"normal":{"x":0,"y":0,"z":0},"vertexNormals":[],"color":{},"vertexColors":[],"vertexTangents":[],"materialIndex":0}]" lacuna.js:344
[{"a":3,"b":0,"c":1,"normal":{"x":0,"y":0,"z":0},"vertexNormals":[],"color":[],"vertexColors":[],"vertexTangents":[],"materialIndex":0},{"a":1,"b":2,"c":3,"normal":{"x":0,"y":0,"z":0},"vertexNormals":[],"color":[],"vertexColors":[],"vertexTangents":[],"materialIndex":0}]]]


?>

				modelGeometry = new THREE.Geometry();
				modelGeometry.vertices = modelVertices
				modelGeometry.faces
					modelGeometry.verticesNeedUpdate = true
					modelGeometry.normalsNeedUpdate = true
					try {
						modelGeometry.computeFaceNormals();
						var modelMesh = new THREE.Mesh(modelGeometry, material);
						modelMesh.material.side = THREE.DoubleSide;

						modelMesh.name =  layername.replace('"', '').replace('"', '') + " " + ids[id].toString();
						console.log(modelMesh.name);
						scene.add(modelMesh);
					}
