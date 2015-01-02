function get_area(a, b, c) {
	p = (a + b + c) / 2
	A = Math.sqrt( p * (p - a) * (p - b) * (p - c) )
	surfacearea = parseFloat(A.toFixed(4))
	return surfacearea
}

function distance(x1, y1, z1, x2, y2, z2) {
	d = Math.sqrt(Math.pow((x2 - x1), 2) +
		Math.pow((y2 - y1), 2) +
		Math.pow((z2 - z1), 2))

	return parseFloat(d.toFixed(3))
}

function get_point_distance(p1, p2) {
	x1 = p1[0]
	y1 = p1[1]
	z1 = p1[2]
	x2 = p2[0]
	y2 = p2[1]
	z2 = p2[2]

	d = Math.sqrt(Math.pow((x2 - x1), 2) +
		Math.pow((y2 - y1), 2) +
		Math.pow((z2 - z1), 2))

	return d
}

function get_object_layer_id(object) {
	if (( object.hasOwnProperty("name")) && (object.name != "")) {
		layer = object.name.split(" ")
		layerName = layer[0]
		layerID = layer[1]
		return [layerName, layerID]
	}
}

function getLayerColour (layername) {
	l = layername.replace('"', '')
	l = l.replace('"', '')
	return $("#" + l + "col").css("background-color")
}