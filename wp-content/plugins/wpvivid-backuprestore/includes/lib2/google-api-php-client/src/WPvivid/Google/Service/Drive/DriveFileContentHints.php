<?php
/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

class WPvivid_Google_Service_Drive_DriveFileContentHints extends WPvivid_Google_Model
{
  public $indexableText;
  protected $thumbnailType = 'WPvivid_Google_Service_Drive_DriveFileContentHintsThumbnail';
  protected $thumbnailDataType = '';

  public function setIndexableText($indexableText)
  {
    $this->indexableText = $indexableText;
  }
  public function getIndexableText()
  {
    return $this->indexableText;
  }
  /**
   * @param WPvivid_Google_Service_Drive_DriveFileContentHintsThumbnail
   */
  public function setThumbnail(WPvivid_Google_Service_Drive_DriveFileContentHintsThumbnail $thumbnail)
  {
    $this->thumbnail = $thumbnail;
  }
  /**
   * @return WPvivid_Google_Service_Drive_DriveFileContentHintsThumbnail
   */
  public function getThumbnail()
  {
    return $this->thumbnail;
  }
}
