<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2008 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
class Local_Import_Controller extends Controller {
  public function index($id) {
    $paths = unserialize(module::get_var("local_import", "authorized_paths"));

    $item = ORM::factory("item", $id);
    access::can("local_import", $item);

    $view = new View("local_import_tree_dialog.html");
    $view->action = url::site("local_import/add/$id");
    $view->hidden = array("csrf" => access::csrf_token(), "base_url" => url::base(true));
    $view->parents = $item->parents();
    $view->album_title = $item->title;

    $tree = new View("local_import_tree.html");
    $tree->data = array();
    $tree->uid = "tree_$id";
    foreach (array_keys($paths) as $path) {
      $tree->data[$path] = array("path" => $path, "is_dir" => true);
    }
    $view->tree = $tree->__toString();
    print $view;
  }

  public function children() {
    access::verify_csrf();

    $path = $this->input->post("path");
    $path =  implode("/", $this->input->post("path"));
    if (!is_readable($path)) {
      kohana::show_404();
    }

    $tree = new View("local_import_tree.html");
    $tree->data = $this->_get_children($path);
    $tree->uid = "tree_" . md5($path);
    print $tree;
  }

  function add($id) {
    access::verify_csrf();

    $parent = ORM::factory("item", $id);
    access::can("local_import", $item);
    if (!$parent->loaded) {
      throw new Exception("@todo BAD_ALBUM");
    }

    $path = $this->input->post("path");

    $base_path = $parent->file_path();
    $source_path = $path[0];
    for ($i = 1; $i < count($path); $i++) {  // skip the first path
      $base_path .= "/$path[$i]";
      $source_path .= "/$path[$i]";
      $pathinfo = pathinfo($source_path);
      set_time_limit(30);
      if (is_dir($source_path)) {
        $album = ORM::factory("item")
          ->where("name", $path[$i])
          ->where("parent_id", $parent->id)
          ->find();
        if (!$album->loaded) {
          $parent = album::create($parent, $path[$i], $path[$i]);
          log::success("content", t("Added album"),
                       html::anchor("albums/{$parent->id}", t("view album")));
          message::success(t("Added album %album_title", array("album_title" => $parent->title)));
        } else {
          $parent = $album;
        }
      } else if (in_array($pathinfo["extension"], array("flv", "mp4"))) {
        $movie =
          movie::create($parent, $source_path, basename($source_path), basename($source_path));
        log::success("content", t("Added a movie"),
                     html::anchor("movies/{$movie->id}", t("view movie")));
        message::success(t("Added movie %movie_title", array("movie_title" => $movie->title)));
      } else {
        $photo =
          photo::create($parent, $source_path, basename($source_path), basename($source_path));
        log::success("content", t("Added a photo"),
                     html::anchor("photos/{$photo->id}", t("view photo")));
        message::success(t("Added photo %photo_title", array("photo_title" => $photo->title)));
      }
    }
  }

  private function _get_children($path) {
    $file_list = array();
    $files = scandir($path);
    foreach ($files as $file) {
      if ($file[0] != ".") {
        $full_path = "$path/$file";
        if (is_dir($full_path)) {
          $file_list[$file] =
            array("path" => $full_path, "is_dir" => true);
        } else {
          $extension = strtolower(substr(strrchr($file, '.'), 1));
          // Make sure the file is readable
          if (is_readable($full_path) &&
              in_array($extension, array("gif", "jpeg", "jpg", "png", "flv", "mp4"))) {
            $file_list[$file] = array("path" => $full_path, "is_dir" => false);
          }
        }
      }
    }
    return $file_list;
  }
}