document.addEventListener("DOMContentLoaded", function () {
  const params = new URLSearchParams(window.location.search);
  if (
    params.get("virtual_card_id") &&
    params.get("post_type") === "statistics"
  ) {
    const blocTotal = document.querySelector(
      "#wpbody-content>.wrap>.subsubsub"
    );
    blocTotal.style.display = "none";
  }
});
