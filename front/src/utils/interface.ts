// 区块链信息的接口
// export interface ChainInfo {
//   name: string
//   hash: string
//   cumulativeSize: number
//   blocks: number
// }

// // 区块链文件格式
// export interface FileInfo {
//   type: string
//   blocks: number
//   size: number
//   hash: string
//   cumulativeSize: number
//   withLocality: boolean
//   name: string
// }

// export interface UserInfo {
//   sId: string
//   sUserName: string
//   sDisplayName: string
//   sAvatar: string
//   sWalletAddress: string
//   token?: number
// }

// export interface NewFileInfo {
//   name: string
//   content: string
// }

// IPFS返回的文件格式
export interface FileInterface {
  Type: string
  Blocks: number
  Size: number
  Hash: string
  CumulativeSize: number
  withLocality: boolean
  name: string
}

export interface UserInterface {
  sId: string
  sUserName: string
  sDisplayName: string
  sAvatar: string
  sWalletAddress: string
  token?: number
  iSex?: string
  sDescription: string
}

export interface ProjectInterface extends FileInterface {
  createAt: number
  sDescription: string
  sId: string
  sUserId: string
  sUserName: string
  sCategoryName?: string,
  iSupportToken: number,
  sProjectName: string
}

export interface ReportInterface {
  createAt: number
  iScore: number
  sDetectTaskId: string
}

export interface IssueInterface {
  issueID: number
  status: number
  title: string
  type: number
  createTime: string
  level: number
  createUser: string
}

export interface CommentInfoInterface{
  commentID: number
  issueID: number
  content: string
  createUser: string
  createTime: string
  isCreator: boolean
}

export interface IssueInfoInterface {
  issueID: number
  status: number
  title: string
  type: number
  content: string
  createTime: string
  level: number
  createUser: string
  isCreator: boolean
  comments: CommentInfoInterface[]
}
