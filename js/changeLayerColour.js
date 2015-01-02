			function changeLayerColour(layername) {
				scene.children.forEach( function(o) {
					colPos = SELECTED.sceneobject.indexOf(o)
					if ( colPos != -1) { SELECTED.color[colPos] = new THREE.Color(getLayerColour(layername)) }
					if ( colPos === -1 && o.hasOwnProperty("children") && o.children.length != 0 && o.name.substring(0, layername.length) === layername ) {
						o.children.forEach( function(c) {
							c.material.color = new THREE.Color(getLayerColour(layername))
							if (c.material.hasOwnProperty("ambient")) { c.material.ambient = new THREE.Color(getLayerColour(layername)) }
						});
					}
					if ( colPos === -1 && o.hasOwnProperty("material") && o.name.substring(0, layername.length) === layername ) {
						o.material.color = new THREE.Color(getLayerColour(layername))
						if (o.material.hasOwnProperty("ambient")) { o.material.ambient = new THREE.Color(getLayerColour(layername)) }
					}
				});
			}

