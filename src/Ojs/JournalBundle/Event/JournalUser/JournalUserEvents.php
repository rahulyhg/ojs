<?php

namespace Ojs\JournalBundle\Event\JournalUser;

final class JournalUserEvents
{
    const LISTED = 'ojs.journal_user.list';

    const PRE_CREATE = 'ojs.journal_user.pre_create';

    const POST_CREATE = 'ojs.journal_user.post_create';

    const PRE_UPDATE = 'ojs.journal_user.pre_update';

    const POST_UPDATE = 'ojs.journal_user.post_update';

    const PRE_DELETE = 'ojs.journal_user.pre_delete';

    const POST_DELETE = 'ojs.journal_user.post_delete';

    const PRE_SUBMIT = 'ojs.journal_user.pre_submit';

    const POST_SUBMIT = 'ojs.journal_user.post_submit';
}
