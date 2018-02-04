<?php
//Includes the needed files
include_once("System/Business/Login/login.php");
include_once("System/Business/Testing/elmUnit.php");
include_once("System/Business/UserManagement/userManager.php");
include_once("System/Business/RoleManagement/roleManagement.php");

class elm_PageLoader {
    private $navBar;
    private $HTML;
    private $currentPage;

    public function __construct()
    {
        $this->navBar = '';
        $this->HTML = '';
        $this->currentPage = NULL;
    }

    /**
     * Loads and prints the content of the current webpage
     */
    public function printPageContent()
    {
        @session_start();

        $this->setCurrentPageId();

        $this->HTML = file_get_contents('Styling/index.html', FILE_USE_INCLUDE_PATH);

        $this->currentPage = new elm_Page();
        $this->currentPage->name = '404 Error';
        $this->currentPage->id = 'elm_404';
        $this->currentPage->content = '<h1>404 Page not found</h1>';

        $this->setNavBar();

        $this->checkGetAndPostValues();

        $this->replaceAllPlaceholders();

        eval('?>' . $this->HTML . '<?php');
    }

    /**elm_Page_Load
     * Replaces any text placeholder in html construct with given value
     * @param string $placeholder The placeholder to replace
     * @param string $value The value to set
     */
    public function replacePlaceholder(string $placeholder, string $value)
    {
        $this->HTML = str_replace($placeholder, $value, $this->HTML);
    }

    /**
     * Executes specific functions depending on the get and post values given
     */
    private function checkGetAndPostValues(){
        GLOBAL $elm_Data;

        /**
         * All pages in the selection are only seen by logged in users
         */

        if(elm_LoginFunctionality::userIsLoggedIn()){
            /**
             * Loads testing page if needed
             */
            if (isset($_GET['elmUnit_Testing'])) {
                $this->currentPage = new elm_Page();
                $this->currentPage->name = 'elmUnit Testing';
                $this->currentPage->id = 'elmUnit_Testing';
                $this->currentPage->content = elmUnit_TestExecutor::executeTests();
            }
        }

        /**
         * Loads login Page if needed
         */
        if (isset($_GET['login'])) {
            $this->currentPage = new elm_Page();
            $this->currentPage->name = 'Login';
            $this->currentPage->id = 'elm_Login';
            $this->currentPage->content = file_get_contents('System/UI/HTML/loginMask.html', FILE_USE_INCLUDE_PATH);
        }

        /**
         * Executes login if needed
         */
        if (isset($_POST['elm_Login_Execute'])) {
            elm_LoginFunctionality::executeLogin($_POST['elm_Login_Username'], $_POST['elm_Login_Password'], $_POST['elm_Login_Password']);
        }

        /**
         * Executes logout if needed
         */
        if (isset($_GET['logout']))
            elm_LoginFunctionality::executeLogout();

        /**
         * Gets triggered after "add new page" is clicked
         */
        if (isset($_GET['addPage_admin'])){
            $this->currentPage = new elm_Page();
            $this->currentPage -> name = 'Add Page';
            $this->currentPage -> id = 'elm_Admin_AddPage';
            $this->currentPage -> content = file_get_contents('System/UI/HTML/addPageMask.php', FILE_USE_INCLUDE_PATH);
        }

        /**
         * Gets triggered after "edit page" is clicked
         * */
        if (isset($_GET['editPage_admin']) && isset($_GET['id'])){
            $this->currentPage = new elm_Page();
            $this->currentPage -> name = 'Edit Page';
            $this->currentPage -> id = 'elm_Admin_EditPage';
            $this->currentPage -> content = file_get_contents('System/UI/HTML/editPageMask.php', FILE_USE_INCLUDE_PATH);
        }

        /**
         * Used to edit and save  a page
         */
        if (isset($_POST['elm_EditPage_Execute_admin'])){
            /**Parameter:
             * $pageID, $pageName, $title, $parentPage, $content, $keywords, $sorting
             **/
            if (isset($_POST['elm_EditPage_Titel']) && isset($_POST['elm_EditPage_Keyword']) && isset($_POST['elm_EditPage_Sorting']) && isset($_POST['elm_EditPage_Content']) && isset($_POST['elm_EditPage_Id'])) {
                $elm_Data->updatePage($_POST['elm_EditPage_Id'], $_POST['elm_EditPage_Titel'], $_POST['elm_EditPage_Content'], $_POST['elm_EditPage_parentPage'], $_POST['elm_EditPage_Keyword'], $_POST['elm_EditPage_Sorting']);
                header("Location: index.php?page=elm_Page_Edit");
            }
            else {
                //TODO: error handling
                echo "error";
            }
        }

        /**
         * Add/create a new page
         */
        if (isset($_POST['elm_addPage_Execute_admin'])){
            if (isset($_POST['elm_addPage_Titel']) && isset($_POST['elm_addPage_Keyword']) && isset($_POST['elm_addPage_Sorting']) && isset($_POST['elm_addPage_Content']) && isset($_POST['elm_addPage_ParentPage'])) {
                if ($elm_Data->createPage($_POST['elm_addPage_Titel'], $_POST['elm_addPage_Content'], $_POST['elm_addPage_ParentPage'], $_POST['elm_addPage_Keyword'], $_POST['elm_addPage_Sorting'])) {
                    header("Location: index.php?page=elm_Page_Edit");
                }
            }
            else {
                //TODO: error handling
                echo "error";
            }
        }

        /**
         * Used to delete a page
         */
        if (isset($_GET['deletePage_admin']) && isset($_GET['id'])) {
            if(isset($_GET['id'])) {
                $elm_Data->deletePage($_GET['id']);
            } else {
                //TODO: error handling
                echo "error";
            }

            header("Location: index.php?page=elm_Page_Edit");
        }

        /**
         * Edits current user values
         */
        if(isset($_POST['elm_EditUser_Execute'])){
            if(elm_UserManager::updateCurrentUser($_POST['elm_EditUser_Username'], $_POST['elm_EditUser_Password'], $_POST['elm_EditUser_Email']))
                header("Location: index.php");
            else{
                //TODO: error handling
                echo "error";
            }

        }

        /**
         * Deletes current user
         */
        if(isset($_GET['elm_EditUser_DeleteCurrentUser'])){
            if(elm_UserManager::deleteCurrentUser())
                header("Location: index.php");
            else{
                //TODO: error handling
                echo "error";
            }
        }

        /**
         * Add/Delete roles
         */
        if(isset($_GET['addRole_admin'])){
            if (isset($_POST['elm_NewRole_Execute_admin'])) {
                if (isset($_POST['elm_AddRole_Name']) && isset($_POST['elm_AddRole_Description'])) {
                    if(elm_RoleManger::addRole($_POST['elm_AddRole_Name'], $_POST['elm_AddRole_Description']))
                        header("Location: index.php?page=elm_RoleManagement");
                    else {
                        //TODO: error handling
                        echo "error";
                    }
                } else {
                    //TODO: error handling
                    echo "error";
                }
            }
            else{
                $this->currentPage = new elm_Page();
                $this->currentPage -> name = 'Add Role';
                $this->currentPage -> id = 'elm_Admin_AddRole';
                $this->currentPage -> content = file_get_contents('System/UI/HTML/addRoleMask.html', FILE_USE_INCLUDE_PATH);
            }
        }

        /**
         * Deletes a given role
         */
        if (isset($_GET['deleteRole_admin']) && isset($_GET['id'])){
            if (elm_RoleManger::deleteRole($_GET['id'])){
                header("Location: index.php?page=elm_RoleManagement");
            }
            else {
                //TODO: error handling
                echo "Not allowed";
            }
        }

        /**
         * Opens add user page
         */
        if(isset($_GET['addUser_admin'])){
            //go to Add UserManagement Page
            $this->currentPage = new elm_Page();
            $this->currentPage -> name = 'Add UserManagement';
            $this->currentPage -> id = 'elm_Admin_AddUser';
            $this->currentPage -> content = file_get_contents('System/UI/HTML/addUserMask.php', FILE_USE_INCLUDE_PATH);
        }

        /**
         * Opens edit user page
         */
        if (isset($_GET['editUser_admin']) && isset($_GET['id'])){
            //go to Edit UserManagement Page
            $elm_Page_CurrentPage = new elm_Page();
            $elm_Page_CurrentPage -> name = 'Edit UserManagement';
            $elm_Page_CurrentPage -> id = 'elm_Admin_AddUser';
            $elm_Page_CurrentPage -> content = file_get_contents('System/UI/HTML/editUserMask.php', FILE_USE_INCLUDE_PATH);
        }

        /**
         * Adds new user
         */
        if (isset($_POST['elm_NewUser_Execute_admin'])) {
            if (isset($_POST['elm_AddUser_Username']) && isset($_POST['elm_AddUser_Email']) && isset($_POST['elm_AddUser_Password']) && isset($_POST['elm_AddUser_Role'])) {
                if(elm_UserManager::addUser($_POST['elm_AddUser_Username'], $_POST['elm_AddUser_Password'], $_POST['elm_AddUser_Email'], $_POST['elm_AddUser_Role']))
                    header("Location: index.php?page=elm_UserManagement");
                else {
                    //TODO: error handling
                    echo "error";
                }
            } else {
                //TODO: error handling
                echo "error";
            }
        }

        /**
         * Deletes existing user
         */
        if (isset($_GET['deleteUser_admin'])){
            //delete UserManagement
            if (isset($_GET['id'])) {
                if(elm_UserManager::deleteUser($_GET['id']))
                    header("Location: index.php?page=elm_UserManagement");
                else {
                    //TODO: error handling
                    echo "error";
                }
            }
            else {
                //TODO: error handling
                echo "error";
            }
        }

        /**
         * Updates user values
         */
        if (isset($_POST['elm_EditUser_Execute_admin'])){
            if (isset($_POST['elm_EditUser_Username']) && isset($_POST['elm_EditUser_Email']) && isset($_POST['elm_EditUser_Password']) && isset($_POST['elm_EditUser_Id'])) {
                if (elm_UserManager::updateUser($_POST['elm_EditUser_Id'], $_POST['elm_EditUser_Username'], $_POST['elm_EditUser_Password'], $_POST['elm_EditUser_Email'])) {
                    header("Location: index.php?page=elm_UserManagement");
                }
                else {
                    //TODO: error handling
                    echo "error";
                }
            }
            else {
                //TODO: error handling
                echo "error";
            }
        }
    }

    /**
     * Creates the nav menu
     */
    private function setNavBar(){
        $menuContent = '';
        $allPages = $this->getAllPages();
        foreach ($allPages as $page){
            if(!isset($page->parentPage) || strlen(str_replace(' ', '', $page->parentPage)) == 0){
                $currentId = $page->id;
                $menuContent = $menuContent . '<div class="dropdown-'. $currentId .'"><a class="dropbtn-' . $currentId . '" href="index.php?page='. $page->id . '">' . $page->name . '</a> <div class="dropdown-content-'. $currentId . '">';
                foreach ($allPages as $subpage){
                    if(isset($subpage->parentPage) && $page->id == $subpage->parentPage){
                        $menuContent = $menuContent. '<a href="index.php?page=' . $subpage->id . '">' . $subpage->name . '</a>';
                    }
                }
                $menuContent = $menuContent . '</div></div>';

                //Adds styling to menu so dropdown works
                $menuContent = $menuContent .
                    '<style>
                    .dropbtn-'.$currentId.' {
                        padding: 16px;
                        font-size: 16px;
                        border: none;
                        margin-bottom: 50px;
                    }
                
                    .dropdown-'. $currentId .' {
                        display: inline-block;
                    }
                   
                    .dropdown-content-'.$currentId.' {
                        display: none;
                        position: absolute;
                        min-width: 50px;
                        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
                        background-color: #333;
                        z-index: 1;
                        margin-top: 50px;
                    }
                
                    .dropdown-content-'.$currentId.' a {
                        padding: 12px 16px;
                        text-decoration: none;
                        display: block;
                    }
                
                    .dropdown-'. $currentId .':hover .dropdown-content-'.$currentId.' {
                        display: block;
                    }
                </style>';

            }
            //Sets page content if current page is in loop
            if($page->id == (string)$_SESSION['elm_Pages_CurrentPageId']){
                $this->setCurrentPage($page);
            }
        }
        $this->navBar = $menuContent;
    }

    /**
     * Sets the current page shown, as a global variable
     * @param $page The page to set
     */
    public function setCurrentPage(elm_Page $page)
    {
        $this->currentPage = $page;
    }

    /**
     * Gets all pages from the database and all predefined pages.
     * @return array An array of all pages as elm_Page objects
     */
    private function getAllPages() : array
    {
        GLOBAL $elm_Data;
        $pages = $elm_Data->getPages();

        if (elm_LoginFunctionality::userIsLoggedIn()) {

            if ($_SESSION['login_role_fk'] == 1) {
                //Adds Admin Page
                $adminpage = new elm_Page();
                $adminpage->id = 'elm_Admin';
                $adminpage->content = file_get_contents('System/UI/HTML/adminPage.php', FILE_USE_INCLUDE_PATH);
                $adminpage->name = 'Admin';
                $adminpage->sorting = 9900;
                array_push($pages, $adminpage);

                //Adds User Management Page
                $userMgmtPage = new elm_Page();
                $userMgmtPage->id = "elm_UserManagement";
                $userMgmtPage->content = file_get_contents('System/UI/HTML/userManagement.php', FILE_USE_INCLUDE_PATH);
                $userMgmtPage->name = "User Management";
                $userMgmtPage->parentPage = 'elm_Admin';
                $userMgmtPage->sorting = 9910;
                array_push($pages, $userMgmtPage);

                //Adds Role Management Page
                $roleMgmtPage = new elm_Page();
                $roleMgmtPage->id = "elm_RoleManagement";
                $roleMgmtPage->content = file_get_contents('System/UI/HTML/roleManagement.php', FILE_USE_INCLUDE_PATH);
                $roleMgmtPage->name = "Role Management";
                $roleMgmtPage->parentPage = 'elm_Admin';
                $roleMgmtPage->sorting = 9920;
                array_push($pages, $roleMgmtPage);
            }
            //Adds Edit Page
            $editPage = new elm_Page();
            $editPage->id = 'elm_Page_Edit';
            $editPage->content = file_get_contents('System/UI/HTML/editPage.php', FILE_USE_INCLUDE_PATH);
            $editPage->name = 'Edit Pages';
            $editPage->parentPage = 'elm_Admin';
            $editPage->sorting = 9930;
            array_push($pages, $editPage);

            //User edit Page
            $userPage = new elm_Page();
            $userPage->id = 'elm_User_Edit';
            $userPage->content = file_get_contents('System/UI/HTML/user.html', FILE_USE_INCLUDE_PATH);
            $userPage->name = $_SESSION['login_user'];
            $userPage->sorting = 9800;
            array_push($pages, $userPage);
        }

        //Orders Array
        usort($pages, function ($a, $b) {
            return strcmp($a->sorting, $b->sorting);
        });

        return $pages;
    }

    /**
     * Sets the id of the current page as a session variable
     * Also sets last page to the session
     * Variables = elm_Pages_CurrentPageId and elm_Pages_LastPageId
     */
    private function setCurrentPageId()
    {
        if (isset($_SESSION['elm_Pages_CurrentPageId'])) {
            $_SESSION['elm_Pages_LastPageId'] = $_SESSION['elm_Pages_CurrentPageId'];
            if (isset($_GET["page"])) {
                $_SESSION['elm_Pages_CurrentPageId'] = $_GET["page"];
            }
            else{
                $_SESSION['elm_Pages_CurrentPageId'] = 1;
            }
        } else{
            $_SESSION['elm_Pages_CurrentPageId'] = 1;
        }
    }

    /**
     * Replaces all placeholders created by slippery elm in the html construct
     */
    private function replaceAllPlaceholders()
    {
        $this->replacePlaceholder("[elm_Page_Content]", $this->currentPage->content);
        $this->replacePlaceholder("[elm_Page_NavBar]", $this->navBar);
        $this->replacePlaceholder("[elm_Page_Id]", $this->currentPage->id);
        $this->replacePlaceholder("[elm_Page_Name]", $this->currentPage->name);

        if (elm_LoginFunctionality::userIsLoggedIn()) {
            $this->replacePlaceholder("[elm_Login_Link]", "index.php?logout");
            $this->replacePlaceholder("[elm_Login_Text]", "Abmelden");
        } else {
            $this->replacePlaceholder("[elm_Login_Link]", "index.php?login");
            $this->replacePlaceholder("[elm_Login_Text]", "Anmelden");
        }
    }
}

?>