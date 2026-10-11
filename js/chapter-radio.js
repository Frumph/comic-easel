/*
 * A comic belongs to exactly one chapter: turn the chapter checklist on the comic editor
 * into radio buttons. Loaded in the footer, so the checklist is already in the page.
 */
jQuery("#chapterschecklist input, .chapters-checklist input").each(function(){this.type="radio"});
