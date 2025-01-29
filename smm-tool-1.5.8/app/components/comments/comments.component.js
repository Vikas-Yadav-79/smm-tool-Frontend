"use strict";

angular.module("comment").component("comment", {
  templateUrl: '../../smm-tool-Frontend/smm-tool-1.5.8/app/components/comments/comments.html',
  bindings: {
    data: '<',
    countImage: '='
  },
  controller: function ($scope, $http) {
    $scope.id = this.data.platform_post_ids;
    $scope.flag = true;
    console.log(this.data);
    switch (this.data.platform) {
      case "Instagram":
        $http.get("http://localhost/smm/smm-tool-Frontend/backend-ci/index.php/instagram/getlikes/" + $scope.id)
          .then(function (response) {
            $scope.likes = response.data.like_count;
            $scope.flag = true;
          })
          .catch(function (error) {
            console.log("Error fetching Instagram login URL:", error);
          });

        $http.get("http://localhost/smm/smm-tool-Frontend/backend-ci/index.php/instagram/comments/" + $scope.id)
          .then(function (response) {
            $scope.likes = response.data.like_count;
            $scope.flag = true;
          })
          .catch(function (error) {
            console.log("Error fetching Instagram login URL:", error);
          });
        break;
      case "Facebook":
        $http.get("http://localhost/smm/smm-tool-Frontend/backend-ci/index.php/facebook/getPostComments", {
          params: { postId: $scope.id }
        })
          .then(function (response) {
            console.log(response.data);
            $scope.flag = true;
          })
          .catch(function (error) {
            console.log("Error fetching Facebook comments:", error);
          });
        $http.get("http://localhost/smm/smm-tool-Frontend/backend-ci/index.php/facebook/getPostLikes", {
          params: { postId: $scope.id }
        })
          .then(function (response) {
            console.log(response.data);
            $scope.likes = response.data.like_count;
            $scope.flag = true;
          })
          .catch(function (error) {
            console.log("Error fetching Facebook likes:", error);
          });
        break;
      case "LinkedIn":

        break;
      default:
        break;
    }

    $scope.goForward = () => {
      if (this.countImage + 1 === $scope.imagePage) {
        return;
      }
      $scope.imagePage++;
    }
    $scope.goBackward = function () {
      $scope.imagePage--;
      console.log($scope.imagePage);
    }
    $scope.imagePage = 0;
    this.analytics = {
      image: 'assets/images/sample-post.jpg',
      comments: [
        {
          comment_id: '1',
          author: 'John Doe',
          text: 'Great post!',
          replies: [
            { author: 'Jane Smith', text: 'I agree!' },
            { author: 'Tom Brown', text: 'Well said!' },
          ],
        },
        {
          comment_id: '2',
          author: 'Alice Johnson',
          text: 'Very informative, thanks!',
          replies: [],
        },
        {
          comment_id: '3',
          author: 'Alice Johnson',
          text: 'Very informative, thanks!',
          replies: [{ author: 'Jane Smith', text: 'I agree!' },
          { author: 'Tom Brown', text: 'Well said!' },],
        },
        {
          comment_id: '4',
          author: 'Alice Johnson',
          text: 'Very informative, thanks!',
          replies: [],
        },
        {
          comment_id: '5',
          author: 'Alice Johnson',
          text: 'Very informative, thanks!',
          replies: [],
        },
      ],
    };
    $scope.cachedShowReply = {};
    $scope.showReply = function (id) {
      if ($scope.cachedShowReply[id] !== undefined) {
        return $scope.cachedShowReply[id];
      }
    };
    $scope.toggleReply = function (id) {
      $scope.cachedShowReply[id] = !$scope.cachedShowReply[id];
    }
    $scope.commnetreplayto = "";
    $scope.selectedToReply = {};
    this.replyToComment = (comment) => {

      console.log(comment);
      $scope.selectedToReply = comment;
      $scope.commnetreplayto = "@" + comment.author;
      document.getElementById("input1").focus();
    };
    $scope.onClickReply = function () {
      var text = document.getElementById("input1").value;
      if (text && text.trim() !== '') {
        switch (this.data.platform) {
          case "Instagram":
            if ($scope.commnetreplayto !== "") {
              $http.get("http://localhost/smm/smm-tool-Frontend/backend-ci/index.php/instagram/reply", {
                message: text,
                comment_id: $scope.selectedToReply.id
              })
                .then(function (response) {
                  alert("replyed successfully");
                })
                .catch(function (error) {
                  console.log("Error replying comments on instagram: ", error);
                });
            } else {

            }
            break;

          case "Facebook":
            if ($scope.commnetreplayto !== "") {
              $http.get("http://localhost/smm/smm-tool-Frontend/backend-ci/index.php/facebook/replyToComment", {
                message: text,
                comment_id: $scope.selectedToReply.id
              })
                .then(function (response) {
                  $scope.likes = response.data.like_count;
                  $scope.flag = true;
                })
                .catch(function (error) {
                  console.log("Error replying comments to facebook:", error);
                });
            } else {

            }
            break;
          case "Linkedin":
            break;
          default:
            break;
        };
      }
    }

  }
});