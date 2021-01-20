<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\FileUpload\Location;

/**
 * Class xvmpEditVideoFormGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpEditVideoFormGUI extends xvmpVideoFormGUI {

	/**
	 * @var xvmpOwnVideosGUI | ilVimpPageComponentPluginGUI
	 */
	protected $parent_gui;
    /**
     * @var array
     */
	protected $medium;


	public function __construct($parent_gui, $mid) {
		// load the video from the api, not from the cache
		xvmpCacheFactory::getInstance()->delete(xvmpMedium::class . '-' . $mid);
		$this->medium = xvmpMedium::getObjectAsArray($mid);

		parent::__construct($parent_gui);

		$this->ctrl->setParameter($this->parent_gui, xvmpMedium::F_MID, $mid);
		$this->setTitle($this->pl->txt('edit_video'));
	}



    protected function initForm() {
        $this->addHiddenIdInput();

        $this->addTitleInput();
        $this->addDescriptionInput();
        $this->addFileInput(false);

        $this->addFormHeader('metadata');
        $this->addCategoriesInput();
        $this->addTagsInput();
        $this->addCustomInputs();

        $this->addFormHeader('access');
        $this->addPublishedInput();
        $this->addMediaPermissionsInput();

        $this->addFormHeader('additional_options');
        $this->addThumbnailInput();
	}

	public function fillForm() {
		$array = $this->medium;
		$array[xvmpMedium::F_CATEGORIES] = array_keys($this->medium[xvmpMedium::F_CATEGORIES]);
		$this->setValuesByArray($array);
	}


	public function saveForm() : bool
    {
		if (parent::saveForm()) {
            // changelog entry
            xvmpCacheFactory::getInstance()->delete(xvmpMedium::class . '-' . $this->medium['mid']);
            $new = xvmpMedium::getObjectAsArray($this->medium['mid']);

            xvmpEventLog::logEvent(xvmpEventLog::ACTION_EDIT, $this->parent_gui->getObjId(), $new, $this->medium);
            return true;
        }

        return false;
	}

    protected function storeVideo()
    {
        xvmpMedium::update($this->data);
        $this->upload_service->cleanUp();
    }

    protected function addCommandButtons() {
		if ($this->parent_gui instanceof xvmpOwnVideosGUI) {
			$this->addCommandButton(xvmpOwnVideosGUI::CMD_UPDATE_VIDEO, $this->lng->txt('save'));
			$this->addCommandButton(xvmpOwnVideosGUI::CMD_CANCEL, $this->lng->txt(xvmpOwnVideosGUI::CMD_CANCEL));
		} else {
			$this->addCommandButton(ilVimpPageComponentPluginGUI::CMD_STANDARD, $this->lng->txt('save'));
			$this->addCommandButton(ilVimpPageComponentPluginGUI::CMD_OWN_VIDEOS, $this->lng->txt(xvmpOwnVideosGUI::CMD_CANCEL));
		}
	}
}
