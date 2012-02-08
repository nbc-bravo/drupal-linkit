<?php

/**
 * Define Linkit file plugin class.
 */
class LinkitPluginFile extends LinkitPluginEntity {

  /**
   *
   */
  function buildDescription($file) {
    $description_array = array();
    //Get image info.
    $imageinfo = image_get_info($file->uri);

    if ($this->conf['image_extra_info']['thumbnail']) {
      $image = $imageinfo ? theme_image_style(array(
          'width' => $imageinfo['width'],
          'height' => $imageinfo['height'],
          'style_name' => 'linkit_thumb',
          'path' => $file->uri,
        )) : '';
    }

    if ($this->conf['image_extra_info']['dimensions'] && !empty($imageinfo)) {
      $description_array[] = $imageinfo['width'] . 'x' . $imageinfo['height'] . 'px';
    }

    $description_array[] = parent::buildDescription($file);

    if ($this->conf['show_scheme']) {
      $description_array[] = file_uri_scheme($file->uri) . '://';
    }

    $description = (isset($image) ? $image : '') . implode('<br />' , $description_array);

    return $description;
  }

  /**
   *
   */
  function buildGroup($file) {
    $group = parent::buildGroup($file);
    if ($this->conf['group_by_scheme']) {
      // Get all stream wrappers.
      $stream_wrapper = file_get_stream_wrappers();
      $group .= ' · ' . $stream_wrapper[file_uri_scheme($file->uri)]['name'];
    }
    return $group;
  }

  /**
   *
   */
  function getQueryInstance() {
    // Call the parent getQueryInstance method.
    parent::getQueryInstance();
    // We only what permanent files.
    $this->query->propertyCondition('status', 1);
  }

  /**
   *
   */
  function buildSettingsForm() {
    $form = parent::buildSettingsForm();

    $form['entity:file']['show_scheme'] = array(
      '#title' => t('Show file scheme'),
      '#type' => 'checkbox',
      '#default_value' => isset($this->conf['show_scheme']) ? $this->conf['show_scheme'] : array()
    );

    $form['entity:file']['group_by_scheme'] = array(
      '#title' => t('Group files by scheme'),
      '#type' => 'checkbox',
      '#default_value' => isset($this->conf['group_by_scheme']) ? $this->conf['group_by_scheme'] : array(),
    );

    $image_extra_info_options = array(
      'thumbnail' => t('Show thumbnails <em>(using the image style !linkit_thumb_link)</em>', array('!linkit_thumb_link' => l('linkit_thumb', 'admin/config/media/image-styles/edit/linkit_thumb'))),
      'dimensions' => t('Show pixel dimensions'),
    );

    $form['entity:file']['image_extra_info'] = array(
      '#title' => t('Images'),
      '#type' => 'checkboxes',
      '#options' => $image_extra_info_options,
      '#default_value' => isset($this->conf['image_extra_info']) ? $this->conf['image_extra_info'] : array('thumbnail', 'dimensions'),
    );

    return $form;
  }


}