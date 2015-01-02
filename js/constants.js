
// Declare constants
var camera, scene, renderer;
var helper;
var intersectedPoint = ""
var ACTION = false;
var SELECT = false;
var MULTISELECT = false
var SELECTED = { sceneobject : [ ], color: [ ] };
var LASTHIGHLIGHTED = ""
var CLICKDISTANCE = false ;
var OBJECTDISTANCE = false;
var AREA = false ;
var BUFFER = false ;
var VERTEX = false ;
var VERTEX_EDIT = false ;
var vertexModel
var vertexMesh = ""
var vertexObject = ""
var vertexHelperGeometry = new THREE.SphereGeometry( 1, 16, 16 );
var vertexHelperMaterial = new THREE.MeshLambertMaterial( {color: 0xCCCCCC, ambient: 0xCCCCCC} );
var vertexHelper = new THREE.Mesh( vertexHelperGeometry, vertexHelper  );
vertexHelper.name = "Helper"
var SELECTED_MATERIAL = new THREE.MeshLambertMaterial({ color: 0xCCCCCC, ambient: 0xCCCCCC, reflectivity: 0});
var EDGE = false
var EDGE_EDIT = false
var edgeHelperOne = new THREE.Mesh( new THREE.SphereGeometry( 0.5, 16, 16 ), new THREE.MeshBasicMaterial( {color: 0xCCCCCC, ambient: 0xCCCCCC} )  );
var edgeHelperTwo = new THREE.Mesh( new THREE.SphereGeometry( 0.5, 16, 16 ), new THREE.MeshBasicMaterial( {color: 0xCCCCCC, ambient: 0xCCCCCC} ) );
//We need to use an Epsilon to stop the triangulator from believeing there are duplicate points
var EPSILON = 0.0005 // 1 meter - 0.1 decimeter 0.01 - centimeter 0.001 milimeter
var ORIGIN = new THREE.Vector3(0, 0, 0)
var editingInProgress = false
var GEOMCLICKED = false
var CENTROID
var LAYEREXTENTS

var overMap // true when the mouse is over the map.  used to allow key presses for zoom, pan etc as the key press event in firefox doesn't work on divs