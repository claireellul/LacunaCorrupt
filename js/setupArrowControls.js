			var controls;
			var controls2;
			var raycaster;
			var flyControls;
function setupArrowControls(){
		controls2.enabled = true;

	}

function animate2() {
				//requestAnimationFrame( animate2 );
				/*controls2.isOnObject( false );
				raycaster.ray.origin.copy( controls2.getObject().position );
				raycaster.ray.origin.y -= 10;
*/
				// this tests whether there is an intersection
				// in our case the objects are teh scene
				//var intersections = raycaster.intersectObjects( objects );
				/*var intersections = raycaster.intersectObjects( scene );
				if ( intersections.length > 0 ) {
					controls2.isOnObject( true );
				}
	*/		//	controls2.update();
			//	renderer.render( scene, camera );
				requestAnimationFrame( animate2 );
				controls2.update();
c
				stats.update();
				render();
		}