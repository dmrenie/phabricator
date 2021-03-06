<?php

final class PhabricatorActionView extends AphrontView {

  private $name;
  private $user;
  private $icon;
  private $href;
  private $disabled;
  private $workflow;
  private $renderAsForm;

  public function setHref($href) {
    $this->href = $href;
    return $this;
  }

  public function setIcon($icon) {
    $this->icon = $icon;
    return $this;
  }

  public function setName($name) {
    $this->name = $name;
    return $this;
  }

  public function setDisabled($disabled) {
    $this->disabled = $disabled;
    return $this;
  }

  public function setWorkflow($workflow) {
    $this->workflow = $workflow;
    return $this;
  }

  public function setRenderAsForm($form) {
    $this->renderAsForm = $form;
    return $this;
  }

  public function setUser(PhabricatorUser $user) {
    $this->user = $user;
    return $this;
  }

  public function render() {

    $icon = null;
    if ($this->icon) {

      $suffix = '';
      if ($this->disabled) {
        $suffix = '-grey';
      }

      require_celerity_resource('sprite-icon-css');
      $icon = phutil_render_tag(
        'span',
        array(
          'class' => 'phabricator-action-view-icon sprite-icon '.
                       'action-'.$this->icon.$suffix,
        ),
        '');
    }

    if ($this->href) {
      if ($this->renderAsForm) {
        if (!$this->user) {
          throw new Exception(
            'Call setUser() when rendering an action as a form.');
        }

        $item = javelin_render_tag(
          'button',
          array(
            'class' => 'phabricator-action-view-item',
          ),
          phutil_escape_html($this->name));

        $item = phabricator_render_form(
          $this->user,
          array(
            'action'    => $this->href,
            'method'    => 'POST',
            'sigil'     => $this->workflow ? 'workflow' : null,
          ),
          $item);
      } else {
        $item = javelin_render_tag(
          'a',
          array(
            'href'  => $this->href,
            'class' => 'phabricator-action-view-item',
            'sigil' => $this->workflow ? 'workflow' : null,
          ),
          phutil_escape_html($this->name));
      }
    } else {
      $item = phutil_render_tag(
        'span',
        array(
          'class' => 'phabricator-action-view-item',
        ),
        phutil_escape_html($this->name));
    }

    $classes = array();
    $classes[] = 'phabricator-action-view';
    if ($this->disabled) {
      $classes[] = 'phabricator-action-view-disabled';
    }

    return phutil_render_tag(
      'li',
      array(
        'class' => implode(' ', $classes),
      ),
      $icon.$item);
  }

  public static function getAvailableIcons() {
    $root = dirname(phutil_get_library_root('phabricator'));
    $path = $root.'/resources/sprite/manifest/icon.json';
    $data = Filesystem::readFile($path);
    $manifest = json_decode($data, true);

    $results = array();
    $prefix = 'action-';
    foreach ($manifest['sprites'] as $sprite) {
      $name = $sprite['name'];
      if (preg_match('/-(white|grey)$/', $name)) {
        continue;
      }
      if (!strncmp($name, $prefix, strlen($prefix))) {
        $results[] = substr($name, strlen($prefix));
      }
    }

    return $results;
  }

}
