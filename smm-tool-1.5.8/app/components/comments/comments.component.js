"use strict";

angular.module("comment").component("comment", {
  templateUrl: '/app/components/comments/comments.html',
  bindings: {
    data: '<',
  },
  controller: function ($scope, $http) {
    $scope.id = this.data.id;
    $scope.flag = true;
    // switch (this.data.platform) {
    //   case "Instagram":
    //     $http.get("http://localhost/codeigniter/index.php/instagram/getlikes/" + $scope.id)
    //       .then(function (response) {
    //         $scope.likes = response.data.like_count;
    //         $scope.flag = true;
    //       })
    //       .catch(function (error) {
    //         console.error("Error fetching Instagram login URL:", error);
    //       });
    //     break;
    //   case "Facebook":

    //     break;
    //   case "LinkedIn":

    //     break;
    //   default:
    //     break;
    // }


    this.analytics = {
      image: 'assets/images/sample-post.jpg',
      comments: [
        {
          author: 'John Doe',
          text: 'Great post!',
          replies: [
            { author: 'Jane Smith', text: 'I agree!' },
            { author: 'Tom Brown', text: 'Well said!' },
          ],
        },
        {
          author: 'Alice Johnson',
          text: 'Very informative, thanks!',
          replies: [],
        },
      ],
    };

    this.replyToComment = function (comment) {
      if (comment.replyText && comment.replyText.trim() !== '') {
        comment.replies.push({
          author: 'You',
          text: comment.replyText.trim(),
        });
        comment.replyText = '';
      }
    };

  }

});