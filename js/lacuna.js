
// Get Centroid First
$.ajax({
	url:"./ajax/centroid.php",
	type:"GET",
	dataType:"json",
	success: function(returnedCentroid) {
		console.log(returnedCentroid)
		var maxX = +returnedCentroid[0]
		var maxY = +returnedCentroid[1]
		var minX = +returnedCentroid[2]
		var minY = +returnedCentroid[3]
		LAYEREXTENTS = [maxX, maxY, minX, minY]

		// X , Y
		X = ((maxX - minX) / 2) + minX
		Y = ((maxY - minY) / 2) + minY
		CENTROID = [X, Y]
	},
	async: "false"
}).done(function() {
			init();
			animate();
			setupEvents();
	}); // end of initial setup script

	var intersectedObject = ""
	var intersectedMesh = ""







