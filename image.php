<?php
//simple file uploading
if (isset($_POST["btn"]) && sizeof($_FILES)) {
    $fileName = basename($_FILES["imgFile"]["name"]);
    $imgType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (
        getimagesize($_FILES["imgFile"]["tmp_name"]) !== false &&
        in_array($imgType, ['jpeg', 'jpg', 'gif', 'png'])
    ) {
        if ($_FILES["imgFile"]["size"] > 1048576) {
            die('File is too large. Max - 1Mb');
        }
        if (move_uploaded_file($_FILES["imgFile"]["tmp_name"], $fileName)) {
            //converting given string to array of rgb values
            $paletteArray = getPaletteArray();

            $imgResource = imagecreatefromstring(file_get_contents($fileName));
            $imgW = imagesx($imgResource);
            $imgH = imagesy($imgResource);

            $returnedImg = imagecreatetruecolor($imgW, $imgH);

            for ($x = 0; $x < $imgW; $x++) {
                for ($y = 0; $y < $imgH; $y++) {
                    $c = imagecolorat($imgResource, $x, $y);
                    if ($c !== false) {
                        $p1[0] = ($c >> 16) & 0xFF;
                        $p1[1] = ($c >> 8) & 0xFF;
                        $p1[2] = $c & 0xFF;
                        list($r, $g, $b) = getSmallestDist($p1, $paletteArray);
                        $closestIndexColor = imagecolorallocate($returnedImg, $r, $g, $b);
                        imagesetpixel($returnedImg, $x, $y, $closestIndexColor);
                    }
                }
            }

            header('Content-Type: image/jpeg');
            imagejpeg($returnedImg);
            imagedestroy($imgResource);
            imagedestroy($returnedImg);
        } else {
            print('Failed to upload the file');
        }
    } else {
        print('Wrong file type');
    }
} else {
    print('No file submited');
}

/**
 * Get 3d distance between points
 *
 * @param $p1
 * @param $p2
 * @return int
 */
function findDistance($p1, $p2)
{
    return ($p2[0] - $p1[0]) ** 2 + ($p2[1] - $p1[1]) ** 2 + ($p2[2] - $p1[2]) ** 2;
}

/**
 * Searching for the smallest distance
 *
 * @param $p1
 * @param $paletteArray
 * @return null
 */
function getSmallestDist($p1, &$paletteArray)
{
    global $points; // store calculation results to improve speed
    if (!isset($points[$p1[0]][$p1[1]][$p1[2]])) {
        $sizeArr = count($paletteArray);
        for ($i = 0; $i < $sizeArr; $i++) {
            $min = findDistance($p1, $paletteArray[$i]);
            if ($i === 0 || $min < $minValue) {
                $minIndex = $i;
                $minValue = $min;
            }
        }
        $res = $paletteArray[$minIndex];
        $points[$p1[0]][$p1[1]][$p1[2]] = $res;
    }
    return $points[$p1[0]][$p1[1]][$p1[2]] ?? null;
}

/**
 * Convert provided string to the palette
 *
 * @return array
 */
function getPaletteArray()
{
    $paletteArray = [];
    $colors = "(41,77,129;192,198,205;249,168,143;230,153,105;220,181,150;231,158,76;163,65,0;182,201,173;225,100,81;88,46,35;244,144,180;219,125,106;151,115,116;224,90,0;119,134,165;201,79,118;109,75,85;150,131,171;115,0,96;73,71,75;207,50,198;187,180,165;78,111,167;224,223,104;153,122,76;31,69,103;242,208,179;219,148,188;49,96,102;97,123,124;89,0,12;195,174,126;240,203,196;171,140,57;181,156,126;200,221,104;121,94,137;54,34,95;81,134,174;160,156,131;238,205,92;77,146,54;55,63,40;222,117,0;102,57,36;216,176,1;169,112,160;30,96,48;255,255,255;28,38,63;1,73,158;223,156,3;50,137,191;218,99,167;166,0,24;195,208,58;108,122,141;235,190,132;218,146,0;49,164,185;182,156,165;124,132,15;210,227,105;122,38,132;224,203,182;140,137,128;92,13,18;10,10,10;113,113,111;140,158,0;170,84,0)";
    $colors = substr($colors, 1, -1);
    $colorRgbArr = explode(';', $colors);
    for ($i = 0; $i < count($colorRgbArr); $i++) {
        list($r, $g, $b) = explode(',', $colorRgbArr[$i]);
        $paletteArray[] = [$r, $g, $b];
    }
    return $paletteArray;
}
