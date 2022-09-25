window.addEventListener("DOMContentLoaded", (event) => {
  console.log("DOM entièrement chargé et analysé");

  // Récupération de l'élément button
  let Links = document.querySelectorAll("[data-delete]");
  console.log(Links);
  for (let link of Links) {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      console.log(this.dataset.token);
      console.log(this.href);
      console.log(this.getAttribute("href"));
      //On demande une comfirmation pour la suppression
      // if(confirm("Voulez-vous supprimer cette image ?")){

      fetch(this.getAttribute("href"), {
        method: "DELETE",
        headers: {
          "X-Requested-With":"XMLHttpRequest",
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ _token: this.dataset.token }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) this.parentElement.remove();
          else alert(data.error);
        })
        .catch((err) => console.log(err));
      // }
    });
  }

  //   $("[data-delete]").click(function (e) {
  //     e.preventDefault();
  //     const url = $(this).attr("href");
  //     const token = $(this).data("token");
  //     if (confirm("Voulez vous supprimer cette image ?")) {
  //       $.ajax({
  //         url: url,
  //         type: "DELETE",
  //         data: { token: token },
  //         success: function (data) {
  //           if (data.success) {
  //             location.reload();
  //           } else {
  //             alert(data.error);
  //           }
  //         },
  //       });
  //     }
  //   });
});
