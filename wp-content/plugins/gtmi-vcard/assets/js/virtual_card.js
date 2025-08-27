/**
 * Hide right bloc to publish or update and hide acf group
 */
document.addEventListener("DOMContentLoaded", function () {
  const params = new URLSearchParams(window.location.search);
  if (params.get("meta_gtmi_vcard")) {
    if (params.get("meta_gtmi_vcard") === "leads") {
      const blocStats = document.querySelector("#gtmi_vcard_statistics");
      blocStats.style.display = "none";
    } else if (params.get("meta_gtmi_vcard") === "statistics") {
      const blocLeads = document.querySelector("#gtmi_vcard_leads");
      blocLeads.style.display = "none";
    }
    const groupAcf = document.querySelector('div[id^="acf-group_"]');
    const blocPublish = document.querySelector("#postbox-container-1");
    document.querySelector("h1").style.display = "none";
    // document.querySelector("a.page-title-action").style.display = "none";
    groupAcf.style.display = "none";
    blocPublish.style.display = "none";
  }
});
