Feature: Manage bbPress Forums

  Background:
    Given a WP install

  Scenario: Delete a forum
    Given these installed and active plugins: bbpress

    When I run `wp bbp forum delete 520`
    Then STDOUT should contain:
      """
      Success: Forum and its topics and replies deleted.
      """

  Scenario: Trash a forum
    When I run `wp bbp forum trash 789`
    Then STDOUT should contain:
      """
      Success: All forum topics trashed.
      """

  Scenario: Untrash a forum
    When I run `wp bbp forum untrash 789`
    Then STDOUT should contain:
      """
      Success: All forum topics untrashed.
      """

  Scenario: Open a forum
    When I run `wp bbp forum open 456`
    Then STDOUT should contain:
      """
      Success: Forum opened.
      """

  Scenario: Close a forum
    When I run `wp bbp forum close 487`
    Then STDOUT should contain:
      """
      Success: Forum closed.
      """

  Scenario: Get permalink of a forum
    When I run `wp bbp forum permalink 500`
    Then STDOUT should contain:
      """
      Success: Forum Permalink: http://site.com/forum-slug
      """
