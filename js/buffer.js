	// 3D BUFFERING

	function Buffer3D(radius, opa, center) {
		geometry = new THREE.SphereGeometry( radius, 32, 32 );
		material = new THREE.MeshBasicMaterial( {color: 0xffff00, transparent: true, opacity: opa });
		sphere = new THREE.Mesh( geometry, material );
		sphere.position = center
		sphere.name = "Buffer Sphere"
		scene.add( sphere );
	}

	function BufferCylinder3D(radiustop, radiusbottom, height, opa, center ) {
		// (radiusTop, radiusBottom, height, radiusSegments, heightSegments, openEnded)
		geometry = new THREE.CylinderGeometry( radiustop, radiusbottom, height, 32 );
		material = new THREE.MeshBasicMaterial( {color: 0xffff00, transparent: true, opacity: opa });
		cylinder = new THREE.Mesh( geometry, material );
		cylinder.position = center
		radians = 90 * (Math.PI / 180)
		cylinder.rotation.x = radians
		cylinder.name = "Buffer Cylinder"
		//rotateAroundObjectAxis(cylinder, new THREE.Vector3(0, 1, 0), radians);
		scene.add( cylinder );
	}


	function BufferBox3D(width, height, depth, opa, center ) {
		// width, height, depth, widthSegments, heightSegments, depthSegments
		geometry = new THREE.BoxGeometry( width, height, depth );
		material = new THREE.MeshBasicMaterial( {color: 0xffff00, transparent: true, opacity: opa });
		box = new THREE.Mesh( geometry, material );
		box.position = center
		box.name = "Buffer Box"
		scene.add( box );
	}
