	// GEOMETRY MIGRATION

	//

		function line_to_pg(l) {
		lg = ""
		vertices = l.geometry.vertices
		vertices.forEach( function(v, i) {
			if (i + 1 == vertices.length) { lg = lg + v.x + " " + v.y + " " + v.z + " -999999" }
			else { lg = lg + v.x + " " + v.y + " " + v.z + " -999999," }
		});

		return "LINESTRING ZM (" + lg + ")"

	}

	function point_to_pg(p) {
		pos = p.position
		x = String(pos.x)
		y = String(pos.y)
		z = String(pos.z)

		return "POINT ZM (" + x + " " + y + " " + z + " -999999)"
	}

	function polygon_to_pg(p) {
		pg = ""
		vertices = p.geometry.vertices
		vertices.forEach ( function(v, i) {
			if (i == 0) {
				fv = v.x + " " + v.y + " " + v.z + " -999999"
				pg = fv + ","
			}

			else if (i + 1 == vertices.length) { pg += fv }
			else { pg = pg + v.x + " " + v.y + " " + v.z + " -999999," }

		});
		return "POLYGON ZM ((" + pg  + "))"
	}

	// POLYGON ZM ((425727.27 564743.289999999 41.6263000000035 nan,425725.7 564743.1 41.6811000000016 nan,
	// 425725.8 564741.85 41.656799999997 nan,425723.4287 564741.6129 41.7403000000049 nan,425708.8 564740.15 41.7562999999936 nan,
	// 425711.4 564714.449999999 41.2596999999951 nan,425709.88 564714.24 41.2602000000043 nan,425706.9 564743 41.8162000000011 nan,
	// 425723.1825 564744.890900001 41.8032999999996 nan,425727.05 564745.34 41.6682000000001 nan,425727.27 564743.289999999 41.6263000000035 nan))

	function object_to_tin(o) {
		tinGeometry = ""
		vertices = o.geometry.vertices
		faces = o.geometry.faces
		//console.log(vertices, faces);
		faces.forEach( function(triangle, i) {
			//console.log(vertices[triangle.a].x, vertices[triangle.a].y, vertices[triangle.a].z)
			firstvertex = String(vertices[triangle.a].x) + " " + String(vertices[triangle.a].y) + " " + String(vertices[triangle.a].z)
			allVertexs = "((" + firstvertex + "," +
						 String(vertices[triangle.b].x) + " " + String(vertices[triangle.b].y) + " " + String(vertices[triangle.b].z) + "," +
						 String(vertices[triangle.c].x) + " " + String(vertices[triangle.c].y) + " " + String(vertices[triangle.c].z) + "," +
						 firstvertex + "))"

			// If last triangle
			//console.log(faces.length , i);
			if (faces.length - 1 === i) {
				tinGeometry = tinGeometry + allVertexs
			}
			else if (faces.length - 1 != i){
				tinGeometry = tinGeometry + allVertexs + ","
			}
		});
		return "TIN Z(" + tinGeometry + ")"
	}


	function threejs_to_tin(obj) {

		// If Object3D
		if ( (obj) && (obj.hasOwnProperty("children")) && (obj.children) && (obj.children.length) ) {
			//If object has children, turn it into a geometry collection

			geomstring = "GEOMETRYCOLLECTION("
			obj.children.forEach( function(child, childIndex) {
				tin = object_to_tin(child)
				if (childIndex === obj.children.length - 1) {
					geomstring = geomstring + tin + ")"
				}
				else if (childIndex != obj.children.length - 1) {
					geomstring = geomstring + tin + ","
				}
			});
			return geomstring
		}

		//If Mesh
		else {
			return object_to_tin(obj)
		}
	}
