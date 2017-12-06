Feature: Manage bbPress subscriptions

  Scenario: Subscription CRUD commands.
    Given a bbPress install

    When I run `wp user create testuser1 testuser1@example.com --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {MEMBER_ID}

    When I run `wp bbp forum create --title="Forum" --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {FORUM_ID}

    When I run `wp bbp subscription add --user-id={MEMBER_ID} --object-id={FORUM_ID}`
    Then STDOUT should contain:
      """
      Success: Subscription successfully added.
      """

    When I run `wp bbp subscription list_users --object-id={FORUM_ID} --format=ids`
    Then STDOUT should contain:
      """
      {MEMBER_ID}
      """

    When I run `wp bbp subscription list_users --object-id={FORUM_ID}`
    Then STDOUT should contain:
      """
      1
      """

    When I run `wp bbp subscription list --user-id={MEMBER_ID} --object=forum --format=count`
    Then STDOUT should contain:
      """
      1
      """

    When I run `wp bbp subscription list --user-id={MEMBER_ID} --object=forum --format=ids`
    Then STDOUT should contain:
      """
      {FORUM_ID}
      """

    When I run `wp bbp subscription remove --user-id={MEMBER_ID} --object-id={FORUM_ID}`
    Then STDOUT should contain:
      """
      Success: Subscription successfully removed.
      """

    When I run `wp bbp subscription remove --user-id={MEMBER_ID} --object-id={FORUM_ID} --yes`
    Then the return code should be 1
