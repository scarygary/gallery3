<?php defined("SYSPATH") or die("No direct script access.") ?>
<script type="text/javascript">
  var GET_CHILDREN_URL = "<?= url::site("server_add/children?path=__PATH__") ?>";
  var START_URL = "<?= url::site("server_add/start?item_id={$item->id}&csrf=$csrf") ?>";
</script>

<div id="gServerAdd">
  <h1 style="display: none;"><?= t("Add Photos to '%title'", array("title" => html::purify($item->title))) ?></h1>

  <p id="gDescription"><?= t("Photos will be added to album:") ?></p>
  <ul class="gBreadcrumbs">
    <? foreach ($item->parents() as $parent): ?>
    <li>
      <?= html::purify($parent->title) ?>
    </li>
    <? endforeach ?>
    <li class="active">
      <?= html::purify($item->title) ?>
    </li>
  </ul>

  <ul id="gServerAddTree" class="gCheckboxTree">
    <?= $tree ?>
  </ul>

  <div id="gServerAddProgress" style="display: none">
    <div class="gProgressBar"></div>
    <div id="gStatus"></div>
  </div>

  <span>
    <button id="gServerAddAddButton" class="ui-state-default ui-state-disabled ui-corner-all"
            disabled="disabled">
      <?= t("Add") ?>
    </button>
    <button id="gServerAddPauseButton" class="ui-state-default ui-corner-all" style="display:none">
      <?= t("Pause") ?>
    </button>
    <button id="gServerAddContinueButton" class="ui-state-default ui-corner-all" style="display:none">
      <?= t("Continue") ?>
    </button>

    <button id="gServerAddCloseButton" class="ui-state-default ui-corner-all">
      <?= t("Close") ?>
    </button>
  </span>

  <script type="text/javascript">
    $("#gServerAdd").ready(function() {
      $("#gServerAdd").gallery_server_add();
    });
  </script>

</div>
