

/**
 * @author mrdoob / http://mrdoob.com/
 */
// this file adapted from the THREE.PointerLockControls code downloaded from here: http://threejs.org/examples/misc_controls_pointerlock.html
// downloaded 25th December 2014
// However the PointerLockControls class has been renamed and much of the functionality removed as it is only the
// actual interaction that is of interest

THREE.ArrowControls = function ( camera ) {
	var scope = this;
	var pitchObject = new THREE.Object3D();
	pitchObject.add( camera );
	var yawObject = new THREE.Object3D();
	yawObject.position.y = 10;
	yawObject.add( pitchObject );

	var moveForward = false;
	var moveBackward = false;
	var moveLeft = false;
	var moveRight = false;

	var isOnObject = false;
	var canJump = false;

	var prevTime = performance.now();

	var velocity = new THREE.Vector3();

	var PI_2 = Math.PI / 2;


	this.onMouseMovePointerLock = function ( event ) {
		var movementX = event.movementX || event.mozMovementX || event.webkitMovementX || 0;
		var movementY = event.movementY || event.mozMovementY || event.webkitMovementY || 0;

		yawObject.rotation.y -= movementX * 0.002;
		pitchObject.rotation.x -= movementY * 0.002;

		pitchObject.rotation.x = Math.max( - PI_2, Math.min( PI_2, pitchObject.rotation.x ) );

	};
	this.getObject = function () {

		return yawObject;

	};

	this.isOnObject = function ( boolean ) {

		isOnObject = boolean;
		canJump = boolean;

	};
	// disable the letters, leave only the arrow keys as options
	this.onKeyDownPointerLock = function ( event ) {
		switch ( event.keyCode ) {
			case 38: // up
	//		case 87: // w
				moveForward = true;
				break;

			case 37: // left
	//		case 65: // a
				moveLeft = true; break;

			case 40: // down
	//		case 83: // s
				moveBackward = true;
				break;

			case 39: // right
	//		case 68: // d
				moveRight = true;
				break;

			case 32: // space
				if ( canJump === true ) velocity.y += 350;
				canJump = false;
				break;

		}

	};

// leave only the arrow keys active, not the letters
	this.onKeyUpPointerLock = function ( event ) {
		switch( event.keyCode ) {
			case 38: // up
//			case 87: // w
				moveForward = false;
				break;

			case 37: // left
//			case 65: // a
				moveLeft = false;
				break;

			case 40: // down
//			case 83: // s
				moveBackward = false;
				break;

			case 39: // right
//			case 68: // d
				moveRight = false;
				break;

		}

	};

	this.update = function () {
		var time = performance.now();
		var delta = ( time - prevTime ) / 1000;
		velocity.x -= velocity.x * 10.0 * delta;
		velocity.z -= velocity.z * 10.0 * delta;
		velocity.y -= 9.8 * 100.0 * delta; // 100.0 = mass

		if ( moveForward ) velocity.z -= 400.0 * delta;
		if ( moveBackward ) velocity.z += 400.0 * delta;

		if ( moveLeft ) velocity.x -= 400.0 * delta;
		if ( moveRight ) velocity.x += 400.0 * delta;

		if ( isOnObject === true ) {

			velocity.y = Math.max( 0, velocity.y );

		}

		yawObject.translateX( velocity.x * delta );
		yawObject.translateY( velocity.y * delta );
		yawObject.translateZ( velocity.z * delta );

		if ( yawObject.position.y < 10 ) {
			velocity.y = 0;
			yawObject.position.y = 10;
			canJump = true;
		}
		prevTime = time;

	};

};
