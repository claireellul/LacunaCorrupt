<?php
	set_time_limit(0);
	include "dbconnect.php";
	include "delaunay.php";
echo(pg_dbname($db));
triangulate('temp');

function triangulate($tablename) {
ini_set('pgsql.allow_persistent',0);
global $dbparams;

$db = pg_connect($dbparams,  PGSQL_CONNECT_FORCE_NEW);
/*	$sql = "SELECT a.polyid, a.globalid, b.n,
	  	  st_x(st_pointn(st_exteriorring(a.geom),b.n)) AS x,
	  	  st_y(st_pointn(st_exteriorring(a.geom),b.n)) AS y,
	  	  st_z(st_pointn(st_exteriorring(a.geom),b.n)) as z
	  	FROM
	(SELECT globalid,  geom, nextval('coordlist') as polyid
	  FROM  trianglepolys where st_astext(triangles) = 'GEOMETRYCOLLECTION EMPTY') AS a, public.num_seq AS b
	  	WHERE b.n <= st_npoints(st_exteriorring(a.geom))
	  	and GeometryType(geom) = 'POLYGON'
ORDER BY globalid, polyid, n";
*/

	$sql = "SELECT string_agg(concat(CAST( x AS text ),',',CAST( y AS text ),',',CAST( z AS text )),',') as coordlistz
FROM (
		SELECT a.polyid, a.globalid, b.n,
			  st_x(st_pointn(st_exteriorring(a.geom),b.n)) AS x,
			  st_y(st_pointn(st_exteriorring(a.geom),b.n)) AS y,
			  st_z(st_pointn(st_exteriorring(a.geom),b.n)) as z
			FROM
		(SELECT globalid,  geom, nextval('coordlist') as polyid
		  FROM  trianglepolys where st_astext(triangles) = 'GEOMETRYCOLLECTION EMPTY' ) AS a, public.num_seq AS b
			WHERE b.n <= st_npoints(st_exteriorring(a.geom))
			and GeometryType(geom) = 'POLYGON'
	ORDER BY globalid, polyid, n) ab group by polyid ";
echo($sql);
	$triangle=new DelaunayTriangulation();

	$query = pg_query($db, $sql);
	$rows = array();
	while ($row = pg_fetch_array($query,NULL, PGSQL_ASSOC)) {
//	pg_result_seek($query, 0);
//	$row = pg_fetch_array($query,NULL, PGSQL_ASSOC);
//$coords = pg_fetch_result($query, 0, 0);
	$coords = $row["coordlistz"];
		echo("<br>");
		//$coords = "529535.100045126,14.0915426124362,529535.100045126,14.9855389188814,529535.543095986,14.9855389188814,529536.046684122,14.0915426124362,529535.100045126,14.0915426124362";
		echo($coords);
		$set=array();
		$tree=explode(',',$coords);
		// remove the last coords which are duplicated
		for ($i=0,$end=count($tree) - 3;$i<$end;$i+=3)
		{
			$set[]=array($tree[$i],$tree[$i+1],$tree[$i+2]);
		}
		echo("<br><Br>");
		print_r($set);
		$triangle->main($set);
		print_r($triangle);

	}
pg_close($db);
}
?>