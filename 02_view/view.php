<?php

/**
 *   SIWECOS CRAWLER
 *
 *   Copyright (C) 2019 Ruhr University Bochum
 *
 *   @author Yakup Ates <Yakup.Ates@rub.de
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

class View{
    private $version = "0.9.8";
    private $model;
    private $controller;
    private $messages;
    private $mode;

    private $crawl_result;

    public function __construct($model, $controller, $mode) {
        $this->model      = $model;
        $this->controller = $controller;
        $this->mode       = $mode;

        $this->printJSON($mode);
    }

    public function getCrawlResult() {
        return $this->crawl_result;
    }
    
    private function printFindings() {
        return $this->model->getLogger()->crawlResult;
    }

