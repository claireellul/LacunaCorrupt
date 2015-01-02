	//CAMERA

	function lookAtPosition(x, y, z) {


	//var yOffset = (LAYEREXTENTS[1] - LAYEREXTENTS[3]);
	//camera.position = new THREE.Vector3(CENTROID[0],LAYEREXTENTS[3]-yOffset,20);

		lookAtVector = new THREE.Vector3(parseFloat(x), parseFloat(y), parseFloat(z))
		camera.position.x = lookAtVector.x;
		camera.position.y = lookAtVector.y - 20;
		camera.position.z = 20; // lookAtVector.z ; // Add 50 meters above position so you can actually see it?
		console.log("look at"+lookAtVector);
	//	controls.target = lookAtVector;
	//	controls.update();
	//	flyControls.update();
		var delta = clock.getDelta();
		camControls.update(0);
	}
