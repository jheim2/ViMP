<?php

declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilObjViMPGUI
 *
 * @author            Theodor Truffer <tt@studer-raimann.ch>
 *
 * @ilCtrl_isCalledBy ilObjViMPGUI: ilRepositoryGUI, ilObjPluginDispatchGUI, ilAdministrationGUI
 * @ilCtrl_Calls      ilObjViMPGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI, ilLearningProgressGUI
 */
class ilObjViMPGUI extends ilObjectPluginGUI
{
    const CMD_SHOW_CONTENT = 'showContent';
    const CMD_PLAY_VIDEO = 'playVideo';
    const CMD_SEARCH_VIDEOS = 'searchVideos';
    const CMD_SEARCH_USER_AJAX = 'searchUserAjax';

    const TAB_CONTENT = 'content';
    const TAB_INFO = 'info_short';
    const TAB_VIDEOS = 'videos';
    const TAB_LEARNING_PROGRESS = 'learning_progress';
    const TAB_LOG = 'log';
    const TAB_SETTINGS = 'settings';
    const TAB_PERMISSION = 'permissions';
    const GET_REF_ID = 'ref_id';
    const GET_VIDEO_ID = 'mid';
    const GET_TIME = 't';
    const CMD_TRANSCODING_PROGRESS = 'getTranscodingProgress';
    /**
     * @var ilViMPPlugin
     */
    protected ilViMPPlugin $pl;
    /**
     * @var ilObjViMP
     */
    protected ilObjViMP $obj;
    /**
     * @var Container
     */
    protected $dic;

    /**
     * ilObjViMPGUI constructor.
     *
     * @param int $a_ref_id
     * @param int $a_id_type
     * @param int $a_parent_node_id
     * @throws ilCtrlException
     */
    public function __construct($a_ref_id = 0, int $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        global $DIC;
        $this->dic = $DIC;
        parent::__construct($a_ref_id, $a_id_type, $a_parent_node_id);
        $this->pl = ilViMPPlugin::getInstance();
    }

    /**
     *
     * @throws ilCtrlException
     */
    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass();
        $cmd = $this->ctrl->getCmd();
        if (!ilObjViMPAccess::hasReadAccess() && $next_class != "ilinfoscreengui" && $cmd != "infoScreen" && $cmd != xvmpGUI::CMD_FILL_MODAL) {
            $this->dic->ui()->mainTemplate()->setOnScreenMessage('failure', $this->pl->txt('access_denied'), true);
            $this->ctrl->returnToParent($this);
        }

        $this->tpl->loadStandardTemplate();

        try {
            switch ($next_class) {
                case 'xvmpcontentgui':
                    if (!$this->ctrl->isAsynch()) {
                        $this->initHeader();
                        $this->setTabs();
                    }
                    $xvmpGUI = new xvmpContentGUI($this);
                    $this->ctrl->forwardCommand($xvmpGUI);
                    $this->tpl->printToStdout();
                    break;
                case 'xvmpsearchvideosgui':
                    if (!$this->ctrl->isAsynch()) {
                        $this->initHeader();
                        $this->setTabs();
                    }
                    $xvmpGUI = new xvmpSearchVideosGUI($this);
                    $this->ctrl->forwardCommand($xvmpGUI);
                    $this->tpl->printToStdout();
                    break;
                case 'xvmpeventloggui':
                    if (!$this->ctrl->isAsynch()) {
                        $this->initHeader();
                        $this->setTabs();
                    }
                    $xvmpGUI = new xvmpEventLogGUI($this);
                    $this->ctrl->forwardCommand($xvmpGUI);
                    $this->tpl->printToStdout();
                    break;
                case 'xvmpsettingsgui':
                    if (!$this->ctrl->isAsynch()) {
                        $this->initHeader();
                        $this->setTabs();
                    }
                    $xvmpGUI = new xvmpSettingsGUI($this);
                    $this->ctrl->forwardCommand($xvmpGUI);
                    $this->tpl->printToStdout();
                    break;
                case 'xvmpselectedvideosgui':
                    if (!$this->ctrl->isAsynch()) {
                        $this->initHeader();
                        $this->setTabs();
                    }
                    $xvmpGUI = new xvmpSelectedVideosGUI($this);
                    $this->ctrl->forwardCommand($xvmpGUI);
                    $this->tpl->printToStdout();
                    break;
                case 'xvmpownvideosgui':
                    if (!$this->ctrl->isAsynch()) {
                        $this->initHeader();
                        $this->setTabs();
                    }
                    $xvmpGUI = new xvmpOwnVideosGUI($this);
                    $this->ctrl->forwardCommand($xvmpGUI);
                    $this->tpl->printToStdout();
                    break;
                case 'xvmplearningprogressgui':
                    if (!$this->ctrl->isAsynch()) {
                        $this->initHeader();
                        $this->setTabs();
                    }
                    $xvmpGUI = new xvmpLearningProgressGUI($this);
                    $this->ctrl->forwardCommand($xvmpGUI);
                    $this->tpl->printToStdout();
                    break;
                case "ilinfoscreengui":
                    if (!$this->ctrl->isAsynch()) {
                        $this->initHeader();
                        $this->setTabs();
                    }
                    $this->checkPermission("visible");
                    $this->infoScreen();    // forwards command
                    $this->tpl->printToStdout();
                    break;
                case 'ilpermissiongui':
                    $this->initHeader(false);
                    parent::executeCommand();
                    break;
                default:
                    // workaround for object deletion; 'parent::executeCommand()' shows the template and leads to "Headers already sent" error
                    if ($next_class == "" && $cmd == 'deleteObject') {
                        $this->deleteObject();
                        break;
                    }
                    parent::executeCommand();
                    break;
            }
        } catch (Exception $e) {
            $this->dic->logger()->root()->logStack(ilLogLevel::ERROR, $e->getMessage());
            $this->dic->ui()->mainTemplate()->setOnScreenMessage('failure', $e->getMessage());
            $this->tpl->printToStdout();
        }

    }

    /**
     * @param $cmd
     */
    public function performCommand($cmd) : void
    {
        switch ($cmd) {
            default:
                $this->$cmd();
                break;
        }
    }

    /**
     * @return bool
     */
    protected function supportsCloning() : bool
    {
        return false;
    }

    /**
     * @param string $a_new_type
     *
     * @return ilPropertyFormGUI
     */
    public function initCreateForm($a_new_type) : ilPropertyFormGUI
    {
        $this->tpl->addCss($this->pl->getAssetURL('default/xvmp_settings.css'));

        $form = parent::initCreateForm($a_new_type);

        // ONLINE
        $input = new ilCheckboxInputGUI($this->lng->txt(xvmpSettingsFormGUI::F_ONLINE), xvmpSettingsFormGUI::F_ONLINE);
        $form->addItem($input);

        // LAYOUT
        $input = new ilRadioGroupInputGUI($this->pl->txt(xvmpSettingsFormGUI::F_LAYOUT), xvmpSettingsFormGUI::F_LAYOUT);
        $option = new ilRadioOption(ilUtil::img($this->pl->getImagePath(xvmpSettingsFormGUI::F_LAYOUT . '_' . xvmpSettings::LAYOUT_TYPE_LIST . '.png')),
            (string) xvmpSettings::LAYOUT_TYPE_LIST);
        $input->addOption($option);
        $option = new ilRadioOption(ilUtil::img($this->pl->getImagePath(xvmpSettingsFormGUI::F_LAYOUT . '_' . xvmpSettings::LAYOUT_TYPE_TILES . '.png')),
            (string) xvmpSettings::LAYOUT_TYPE_TILES);
        $input->addOption($option);
        $option = new ilRadioOption(ilUtil::img($this->pl->getImagePath(xvmpSettingsFormGUI::F_LAYOUT . '_' . xvmpSettings::LAYOUT_TYPE_PLAYER . '.png')),
            (string) xvmpSettings::LAYOUT_TYPE_PLAYER);
        $input->addOption($option);
        $input->setValue((string) xvmpSettings::LAYOUT_TYPE_LIST);
        $form->addItem($input);

        return $form;
    }

    function afterSave(ilObject $newObj) : void
    {
        if ($_POST[xvmpSettingsFormGUI::F_ONLINE] || $_POST[xvmpSettingsFormGUI::F_LAYOUT]) {
            /** @var xvmpSettings $settings */
            $settings = xvmpSettings::find($newObj->getId());
            $settings->setIsOnline((int) $_POST[xvmpSettingsFormGUI::F_ONLINE]);
            $settings->setLayoutType((int) $_POST[xvmpSettingsFormGUI::F_LAYOUT]);
            $settings->update();
        }
        parent::afterSave($newObj);
    }

    /**
     * @param bool $render_locator
     */
    protected function initHeader($render_locator = true)
    {
        if ($render_locator) {
            $this->setLocator();
        }

        $this->tpl->setTitleIcon(ilObjViMP::_getIcon($this->object_id));
        $this->tpl->setTitle($this->object->getTitle());
        $this->tpl->setDescription($this->object->getDescription());

        if (!xvmpSettings::find($this->obj_id)->getIsOnline()) {
            require_once('./Services/Object/classes/class.ilObjectListGUIFactory.php');
            /**
             * @var $list_gui ilObjViMPListGUI
             */
            $list_gui = ilObjectListGUIFactory::_getListGUIByType('xvmp');
            $this->tpl->setAlertProperties($list_gui->getAlertProperties());
        }

//		$this->tpl->setTitleIcon(ilObjViMP::_getIcon($this->object_id));
        $this->tpl->setPermanentLink('xvmp', (int) $_GET['ref_id']);
    }

    /**
     * @return bool
     */
    protected function setTabs() : void
    {
        $this->tabs_gui->addTab(self::TAB_CONTENT, $this->pl->txt(self::TAB_CONTENT),
            $this->ctrl->getLinkTargetByClass(xvmpContentGUI::class, xvmpContentGUI::CMD_STANDARD));
        $this->tabs_gui->addTab(self::TAB_INFO, $this->pl->txt(self::TAB_INFO),
            $this->ctrl->getLinkTargetByClass(ilInfoScreenGUI::class));

        if (ilObjViMPAccess::hasWriteAccess()) {
            $this->tabs_gui->addTab(self::TAB_VIDEOS, $this->pl->txt(self::TAB_VIDEOS),
                $this->ctrl->getLinkTargetByClass(xvmpSearchVideosGUI::class, xvmpSearchVideosGUI::CMD_STANDARD));
        } else {
            if (ilObjViMPAccess::hasUploadPermission()) {
                $this->tabs_gui->addTab(self::TAB_VIDEOS, $this->pl->txt(self::TAB_VIDEOS),
                    $this->ctrl->getLinkTargetByClass(xvmpOwnVideosGUI::class, xvmpOwnVideosGUI::CMD_STANDARD));
            }
        }

        if (ilLearningProgressAccess::checkAccess($this->object->getRefId()) && xvmpSettings::find($this->obj_id)->getLPActive()) {
            $this->tabs_gui->addTab(self::TAB_LEARNING_PROGRESS, $this->lng->txt(self::TAB_LEARNING_PROGRESS),
                $this->ctrl->getLinkTargetByClass(xvmpLearningProgressGUI::class,
                    xvmpLearningProgressGUI::CMD_STANDARD));

        }

        if (ilObjViMPAccess::hasWriteAccess()) {
            $this->tabs_gui->addTab(self::TAB_LOG, $this->pl->txt(self::TAB_LOG),
                $this->ctrl->getLinkTargetByClass(xvmpEventLogGUI::class, xvmpEventLogGUI::CMD_STANDARD));
        }

        if (ilObjViMPAccess::hasWriteAccess()) {
            $this->tabs_gui->addTab(self::TAB_SETTINGS, $this->pl->txt(self::TAB_SETTINGS),
                $this->ctrl->getLinkTargetByClass(xvmpSettingsGUI::class, xvmpSettingsGUI::CMD_STANDARD));
        }

        if ($this->checkPermissionBool("edit_permission")) {
            $this->tabs_gui->addTab("perm_settings", $this->dic->language()->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass(array(
                    get_class($this),
                    "ilpermissiongui",
                ), "perm"));
        }
    }

    /**
     * @param $a_target
     */
    public static function _goto($a_target) : void
    {
        global $DIC;
        $DIC->ctrl()->setTargetScript('ilias.php');
        $id = explode("_", $a_target[0]);

        $DIC->ctrl()->setParameterByClass(xvmpContentGUI::class, self::GET_REF_ID, $id[0]);

        if (isset($id[1])) {
            if (isset($id[2])) {
                // time
                $DIC->ctrl()->setParameterByClass(xvmpContentGUI::class, self::GET_TIME, (int) $id[2]);
            }
            $DIC->ctrl()->setParameterByClass(xvmpContentGUI::class, self::GET_VIDEO_ID, (int) $id[1]);
            $DIC->ctrl()->redirectByClass([ilObjPluginDispatchGUI::class, self::class, xvmpContentGUI::class],
                xvmpContentGUI::CMD_PLAY_VIDEO);
        }
        parent::_goto($a_target);
    }

    /**
     * called by the button to test connection inside the plugin config
     */
    public function testConnectionAjax()
    {
        $apikey = $_POST['apikey'];
        $apiurl = $_POST['apiurl'];

        $xvmpCurl = new xvmpCurl(rtrim($apiurl, '/') . '/' . ltrim(xvmpRequest::VERSION, '/'));
        $xvmpCurl->addPostField('apikey', $apikey);
        try {
            $xvmpCurl->post();
            echo "Connection OK";
            exit;
        } catch (Exception $e) {
            $message = 'No Connection, Status Code ' . $e->getCode();
            switch ($e->getCode()) {
                case 401:
                    $message .= ' - No Authorization, possibly wrong API-Key';
                    break;
                case 404:
                    $message .= ' - Not Found, possibly wrong relative URL';
                    break;
                case 500:
                    $message .= ' - Internal Server Error, possibly wrong URL';
                    break;
            }
            echo $message;
            exit;
        }
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return ilViMPPlugin::XVMP;
    }

    /**
     * @return string
     */
    public function getAfterCreationCmd() : string
    {
        return self::CMD_SHOW_CONTENT;
    }

    /**
     * @return string
     */
    public function getStandardCmd() : string
    {
        return self::CMD_SHOW_CONTENT;
    }

    /**
     *
     */
    public function showContent()
    {
        $this->ctrl->redirectByClass(xvmpContentGUI::class, xvmpContentGUI::CMD_STANDARD);
    }

    /**
     *
     */
    public function searchVideos()
    {
        $this->ctrl->redirectByClass(xvmpSearchVideosGUI::class, xvmpSearchVideosGUI::CMD_STANDARD);
    }

    /**
     * ajax
     */
    public function searchUserAjax()
    {
        $username = $_GET['username'];
        $response = xvmpRequest::extendedSearch(array(
            'searchrange' => 'user',
            'title' => $username,
        ))->getResponseBody();
        echo $response;
        exit;
    }

    /**
     * AJAX call
     */
    protected function getTranscodingProgress()
    {
        try {
            $transcodingProgress = xvmpRequest::getTranscodingProgress(filter_input(INPUT_GET, 'mid',
                FILTER_VALIDATE_INT), 2);
            echo $transcodingProgress;
        } catch (xvmpException $e) {
            xvmpCurlLog::getInstance()->write($e->getMessage());
        }
        exit;
    }

    /**
     *
     */
    public function getPicture()
    {
        $key = $_GET['key'];
        // TODO: implement picture wrapper, if api action is implemented
    }

    /**
     * async auto complete method for user filter in search table
     */
    public function addUserAutoComplete()
    {
        include_once './Services/User/classes/class.ilUserAutoComplete.php';
        $auto = new ilUserAutoComplete();
        $auto->setSearchFields(array('login', 'firstname', 'lastname', 'email'));
        $auto->setResultField('login');
        $auto->enableFieldSearchableCheck(false);
        $auto->setMoreLinkAvailable(true);

        if (($_REQUEST['fetchall'])) {
            $auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
        }

        $list = $auto->getList($_REQUEST['term']);

        echo $list;
        exit();
    }
}
