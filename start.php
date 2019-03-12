<?php
/**
 *   SIWECOS CRAWLER
 *
 *   Copyright (C) 2019 Ruhr University Bochum
 *
 *   @author Yakup Ates <Yakup.Ates@rub.de>
 *
 *   This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

include '00_control/control.php';
include '01_model/model.php';
include '02_view/view.php';

// Only report errors (no warnings etc)
error_reporting(E_ERROR);

$DEFAULT_LIMITS = array("mDepth" => 1, "mCount" => 40);

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents("php://input"));

    /* Exit if JSON can not be decoded. */
    if ($data === NULL) {
        $result["name"] = "SIWECOS-CRAWLER";
        $result["hasError"] = TRUE;

        $result["tests"][] = NULL;

        error_log("[-] [SIWECOS-CRAWLER]");
        error_log("  [-] Given JSON could not be decoded.", 0);
        error_log("      name: " . $result["name"], 0);
        error_log("      hasError: " . $result["hasError"], 0);
        error_log("      errorMessage: {", 0);
        error_log("          \"placeholder\": \"JSON_DECODE_ERROR\"", 0);
        error_log("          \"values\": {", 0);
        error_log("                \"json\":" . file_get_contents("php://input"), 0);
        error_log("          }", 0);
        error_log("          \"tests\": null", 0);
        error_log("      }", 0);

        return;
    }

    $controller = new Control($data->url, $data->userAgent, $DEFAULT_LIMITS);
    $controller->setDangerLevel($data->dangerLevel);
    $controller->setCallbackurls($data->callbackurls);
    $controller->setUserAgent($data->userAgent);
    $controller->setMaxDepth($data->maxDepth);
    $controller->setMaxCount($data->maxCount);

    if (isset($data->profile)) {
        $profile = $data->profile;
    } else {
        $profile = "internal";
    }

    $model = new Model($controller, $profile);

    $view = new View($model, $controller, "POST");
} else if ($_SERVER['REQUEST_METHOD'] == "GET") {
    if (!isset($_GET['url']) || empty($_GET['url'])) {
        /**
         * No URL is set or given. The client probably just entered the site.
         * Here we set the starting state.
         */
        return;
    } else {
        /**
         * The controller gets the input data by the user.
         * The controller decides whether the URL is valid and whether the source
         * code will be analysed
         */
        $controller = new Control($_GET['url'], "", $DEFAULT_LIMITS);
        if (isset($_GET['mdepth'])) {
            $controller->setMaxDepth(intval($_GET['mdepth']));
        }

        if (isset($_GET['mcount'])) {
            $controller->setMaxCount(intval($_GET['mcount']));
        }

        if (isset($_GET['profile'])) {
            $profile = $_GET['profile'];
        } else {
            $profile = "internal";
        }

        $model = new Model($controller, $profile);

        $view = new View($model, $controller, "GET");
    }
}

?>