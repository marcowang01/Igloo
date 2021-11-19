const user1Brief = {
    "_id" : 1,
    "fullname" : "Marco Wang",
    "username" : "marco",
    "avatar" : "https://www.w3schools.com/css/img_lights.jpg",
    "isMe" : true,
    "isFollowing" : true
};
const user2Brief = {
    "_id" : 2,
    "fullname" : "Marco Wang2",
    "username" : "marco2",
    "avatar" : "https://www.w3schools.com/css/img_5terre.jpg",
    "isFollowing" : true
};
const user3Brief = {
    "_id" : 3,
    "fullname" : "Marco Wang3",
    "username" : "marco3",
    "avatar" : "https://www.w3schools.com/css/img_forest.jpg",
    "isFollowing" : false
};

const post1 = {
    "_id" : 1,
    "user" : user2Brief,
    "creator" : user2Brief,
    "files" : ["https://www.w3schools.com/images/picture.jpg"],
    "caption" : "this is post 1",
    "likesCount" : 3,
    "commentsCount" : 2,
    "isMine" : false,
    "isLiked" : true,
    "isSaved" : false,
    "blockStatus" : "NFT",
    "rewards" : 1.00001,
    "createdAt" : "2021-8-8 00:00:00",
    "comments" : [{
        "_id": 1,
        "user" : user2Brief,
        "text" : "this is comment 1"
    }]
};
const post2 = {"_id": 2,
    "user" : user2Brief,
    "creator" : user2Brief,
    "files" : ["https://www.w3schools.com/css/paris.jpg"],
    "caption" : "this is post 2",
    "likesCount" : 0,
    "commentsCount" : 1,
    "isMine" : false,
    "isLiked" : false,
    "isSaved" : false,
    "blockStatus" : "NFT",
    "rewards" : 1.00001,
    "createdAt" : "2021-7-7 00:00:00",
    "comments" : [{
        "_id": 2,
        "user" : user2Brief,
        "text" : "this is comment 2"
    }]};
const post3 = {
    "_id" : 3,
    "user" : user1Brief,
    "creator" : user1Brief,
    "files" : ["https://www.w3schools.com/html/pic_trulli.jpg"],
    "caption" : "this is post 3",
    "likesCount" : 2,
    "commentsCount" : 2,
    "isMine" : true,
    "isLiked" : true,
    "isSaved" : false,
    "blockStatus" : "NFT",
    "rewards" : 1.00001,
    "createdAt" : "2021-6-6 00:00:00",
    "comments" : [
        {
            "_id": 3,
            "user" : user3Brief,
            "text" : "this is comment 3"
        },
        {
            "_id": 4,
            "user" : user3Brief,
            "text" : "this is comment 4"
        }
    ]
};
const post4 = {
    "_id" : 4,
    "user" : user1Brief,
    "creator" : user1Brief,
    "files" : ["https://www.w3schools.com/html/img_chania.jpg"],
    "caption" : "this is post 4",
    "likesCount" : 2,
    "commentsCount" : 0,
    "isMine" : true,
    "isLiked" : false,
    "isSaved" : false,
    "blockStatus" : "NFT",
    "rewards" : 1.00001,
    "createdAt" : "2021-5-5 00:00:00",
    "comments" : []
};
const post5 = {
    "_id" : 5,
    "user" : user1Brief,
    "creator" : user3Brief,
    "files" : ["https://www.w3schools.com/html/img_girl.jpg"],
    "caption" : "this is post 5",
    "likesCount" : 1,
    "commentsCount" : 0,
    "isMine" : false,
    "isLiked" : true,
    "isSaved" : true,
    "blockStatus" : "NFT",
    "rewards" : 1.00001,
    "createdAt" : "2021-4-4 00:00:00",
    "comments" : []
};

const user1 = {
    "_id" : 1,
    "fullname" : "Marco Wang",
    "username" : "marco",
    "avatar" : "https://www.w3schools.com/css/img_lights.jpg",
    "isMe" : true,
    "bio" : "this is my bio",
    "postCount" : 2,
    "website" : "www.iglooisawesome.com",
    "followingCount" : 1,
    "followersCount" : 2,
    "followers" : [user2Brief, user3Brief],
    "following" : [user2Brief],
    "posts" : [post3, post4],
    "savedPosts" : [post3, post4, post5]
};
const user2 = {
    "_id" : 2,
    "fullname" : "Marco Wang2",
    "username" : "marco2",
    "avatar" : "https://www.w3schools.com/css/img_5terre.jpg",
    "isFollowing" : true,
    "isMe" : false,
    "bio" : "this is my bio 2",
    "postCount" : 2,
    "website" : "www.iglooisawesome2.com",
    "followingCount" : 1,
    "followersCount" : 1,
    "followers" : [user1Brief],
    "following" : [user1Brief],
    "posts" : [post1, post2],
    "savedPosts" : [post1, post2]
};
const user3 = {
    "_id" : 3,
    "fullname" : "Marco Wang3",
    "username" : "marco3",
    "avatar" : "https://www.w3schools.com/css/img_forest.jpg",
    "isFollowing" : true,
    "isMe" : false,
    "bio" : "this is my bio 3",
    "postCount" : 0,
    "website" : "www.iglooisawesome3.com",
    "followingCount" : 1,
    "followersCount" : 0,
    "followers" : [],
    "following" : [user1Brief],
    "posts" : [post5],
    "savedPosts" : []
}

export {
    user1, user2, user3,
    user1Brief, user2Brief, user3Brief,
    post1, post2, post3, post4, post5
};