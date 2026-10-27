<?php

namespace App\Controllers\Web;

use App\Controllers\Web\MGM\MemberGetMemberController as ModularMemberGetMemberController;

/**
 * Backward compatibility shim for MemberGetMemberController.
 * Extends App\Controllers\Web\MGM\MemberGetMemberController.
 */
class MemberGetMemberController extends ModularMemberGetMemberController
{
}
