<template>
  <div class="container">
    <!-- <md-tabs md-centered class="md-transparent">
      <md-tab md-label="项目创建" @click="onCreateStatus">
        <md-card>
          <md-card-content>
            <h3>创建一个新的项目</h3>
            <md-divider></md-divider>
            <div class="create-panel">
              <div class="field">
                <div class="owner">
                  <div>
                    <md-field>
                      <label>创建者</label>
                      <div class="owner-wrapper">
                        <img :src="avatar"
                            alt="头像"
                            class="avatar">
                        <md-input v-model="userName"
                                  disabled></md-input>
                      </div>
                    </md-field>
                  </div>
                  <strong>&nbsp;/&nbsp;</strong>
                  <div style="display:flex;align-items:center">
                    <div>
                      <md-icon class="fa fa-inbox"></md-icon>
                    </div>
                    <md-field md-clearable>
                      <label>项目名称*</label>
                      <md-input v-model="projectName"></md-input>
                    </md-field>
                  </div>
                </div>
                <p># 一个好的项目名称是简短并且容易记忆的</p>
              </div>
              <div class="field">
                <div>
                  <div>
                    <md-icon class="fa fa-file-code-o"></md-icon>
                  </div>
                  <md-field>
                    <label for="movie">
                      项目类型*
                    </label>
                    <md-select v-model="selectCategory"
                              name="movie"
                              id="category">
                      <md-option v-for="category in categoryList"
                                :value="category"
                                :key="category">{{ category }}</md-option>
                    </md-select>
                  </md-field>
                </div>
                <p># 项目类型在索引时提供帮助</p>
              </div>
              <div class="field">
                <div>
                  <div>
                    <md-icon class="fa fa-file-text-o"></md-icon>
                  </div>
                  <md-field>
                    <label>

                      项目描述
                    </label>
                    <md-textarea v-model="description"></md-textarea>
                  </md-field>
                </div>
                <p># 项目描述可以让人了解您的项目</p>
              </div>
              <div class="field">
                <md-field>
                  <label>初始化项目</label>
                  <md-file placeholder="选择初始化项目的文件（限选一个）"
                          @md-change="onFileChange" />
                </md-field>
                <p># 支持上传文本文件/图片/docx/doc/pdf等类型的资源文件和zip/rar格式的压缩包</p>
              </div>

              <div class="field">
                <md-button class="md-raised md-primary"
                          @click="onCreate">立刻创建</md-button>
              </div>
            </div>
          </md-card-content>
        </md-card>
      </md-tab>

      <md-tab md-label="项目拉取" @click="onPullStatus">
        <md-card>
          <md-card-content>
            <h3>拉取git项目</h3>
            <md-divider></md-divider>
            <div class="create-panel">
              <div class="field">
                <div class="owner">
                  <div>
                    <md-field>
                      <label>创建者</label>
                      <div class="owner-wrapper">
                        <img :src="avatar"
                            alt="头像"
                            class="avatar">
                        <md-input v-model="userName"
                                  disabled></md-input>
                      </div>
                    </md-field>
                  </div>
                  <strong>&nbsp;/&nbsp;</strong>
                  <div style="display:flex;align-items:center">
                    <div>
                      <md-icon class="fa fa-inbox"></md-icon>
                    </div>
                    <md-field md-clearable>
                      <label>项目名称*</label>
                      <md-input v-model="projectName"></md-input>
                    </md-field>
                  </div>
                </div>
                <p># 一个好的项目名称是简短并且容易记忆的</p>
              </div>
              <div class="field">
                <div>
                  <md-field>
                    <label for="movie">
                      项目类型*
                    </label>
                    <md-select v-model="selectCategory"
                              name="movie"
                              id="category">
                      <md-option v-for="category in categoryList"
                                :value="category"
                                :key="category">{{ category }}</md-option>
                    </md-select>
                  </md-field>
                </div>
                <p># 项目类型在索引时提供帮助</p>
              </div>


              <div class="field">
                <md-field>
                  <label for="repo">
                        仓库类型*
                  </label>
                  <md-select v-model="selectRepoType"
                            name="repo"
                            id="repoType">
                    <md-option v-for="repo in repoList"
                              :value="repo"
                              :key="repo">{{ repo }}</md-option>
                  </md-select>
                </md-field>
              </div>
              <div class="field">
                <div>
                  <md-field>
                    <label>
                      仓库地址*
                    </label>
                    <md-input v-model="projectAddress"></md-input>
                  </md-field>
                </div>
                <p># 仓库所在的url地址</p>
              </div>
              <template v-if="isGitee && isPull">
                <div class="field">
                  <div>
                    <md-field>
                      <label>
                        项目主人*
                      </label>
                      <md-input v-model="projectOwner"></md-input>
                    </md-field>
                  </div>
                  <p># 创建gitee仓库的用户名</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>
                        仓库名*
                      </label>
                      <md-input v-model="repoName"></md-input>
                    </md-field>
                  </div>
                  <p># 创建gitee仓库的空间名</p>
                </div>
              </template>

              <template v-if="isGithub && isPull">
                <div class="field">
                  <div>
                    <md-field>
                      <label>
                        github用户名*
                      </label>
                      <md-input v-model="projectOwner"></md-input>
                    </md-field>
                  </div>
                  <p># 请输入github用户名</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>
                        仓库名*
                      </label>
                      <md-input v-model="repoName"></md-input>
                    </md-field>
                  </div>
                  <p># 创建的github仓库名</p>
                </div>
              </template>

              <template v-else-if="isGitlab && isPull">
                <div class="field">
                  <div>
                    <md-field>
                      <label>
                        gitlab项目id*
                      </label>
                      <md-input v-model="projectID"></md-input>
                    </md-field>
                  </div>
                  <p># 创建gitlab项目时的id</p>
                </div>
              </template>

              <div class="field">
                <div>
                  <md-field>
                    <label>
                      用户私钥*
                    </label>
                    <md-input v-model="projectToken"></md-input>
                  </md-field>
                </div>
                <p># 在用户设置中获取访问私钥</p>
              </div>

              <div class="field">
                <div>
                  <md-field>
                    <label>
                      仓库分支*
                    </label>
                    <md-input v-model="projectRef"></md-input>
                  </md-field>
                </div>
                <p># 请输入拉取仓库的分支</p>
              </div>

              <div class="field">
                <div>

                  <md-field>
                    <label>
                      项目描述
                    </label>
                    <md-textarea style="min-height:auto" v-model="description"></md-textarea>
                  </md-field>
                </div>
                <p># 项目描述可以让人了解您的项目</p>
              </div>
              <div class="field">
                <div>
                  <md-button class="md-raised md-primary"
                          @click="onPull">立刻拉取</md-button>
                </div>
              </div>
            </div>
          </md-card-content>
        </md-card>
      </md-tab>
    </md-tabs>-->
    <md-card>
      <md-card-content>
        <h3>创建一个新的项目</h3>
        <md-divider></md-divider>
        <div class="create-panel">
          <div class="field">
            <div class="owner">
              <div>
                <md-field>
                  <label>创建者</label>
                  <div class="owner-wrapper">
                    <img :src="avatar" alt="头像" class="avatar" />
                    <md-input v-model="userName" disabled></md-input>
                  </div>
                </md-field>
              </div>
              <strong>&nbsp;/&nbsp;</strong>
              <div style="display:flex;align-items:center">
                <div>
                  <md-icon class="fa fa-inbox"></md-icon>
                </div>
                <md-field md-clearable>
                  <label>项目名称*</label>
                  <md-input v-model="projectName"></md-input>
                </md-field>
              </div>
            </div>
            <p># 一个好的项目名称是简短并且容易记忆的</p>
          </div>
          <div class="field">
            <div>
              <div>
                <md-icon class="fa fa-file-code-o"></md-icon>
              </div>
              <md-field>
                <label for="movie">项目类型*</label>
                <md-select v-model="selectCategory" name="movie" id="category">
                  <md-option
                    v-for="category in categoryList"
                    :value="category"
                    :key="category"
                  >{{ category }}</md-option>
                </md-select>
              </md-field>
            </div>
            <p># 项目类型在索引时提供帮助</p>
          </div>
          <div class="field">
            <div>
              <div>
                <md-icon class="fa fa-file-text-o"></md-icon>
              </div>
              <md-field>
                <label>项目描述</label>
                <md-textarea v-model="description"></md-textarea>
                <!-- <md-icon class="fa fa-file-text"></md-icon> -->
              </md-field>
            </div>
            <p># 项目描述可以让人了解您的项目</p>
          </div>
          <div class="field">
            <md-field>
              <label>初始化项目</label>
              <md-file placeholder="选择初始化项目的文件（限选一个）" @md-change="onFileChange" />
            </md-field>
            <p># 支持上传文本文件/图片/docx/doc/pdf等类型的资源文件和zip/rar格式的压缩包</p>
          </div>

          <div class="field">
            <md-button class="md-raised md-primary" @click="onCreate">立刻创建</md-button>
          </div>
        </div>
      </md-card-content>
    </md-card>
    <!-- <md-dialog-prompt :md-active="showCreateDialog"
                      v-model="password"
                      md-title="请输入您的账户密码"
                      md-input-placeholder="由8-20位的字母和数字组成"
                      md-cancel-text="取消"
                      md-confirm-text="创建"
                      @md-confirm="onConfirmCreate"
                      @md-cancel="onCancelCreate" /> -->
    <md-dialog :md-active.sync="showCreateDialog" class="tx_dialog">
      <md-dialog-title>发起项目创建交易</md-dialog-title>
      <md-field>
        <label>账户</label>
        <md-input v-model="readOnlyUserName" readonly class="readonly_input"></md-input>
      </md-field>
      <md-field>
        <label>账户地址</label>
        <md-input v-model="readOnlyAddress" readonly class="readonly_input"></md-input>
      </md-field>
      <md-field>
        <label>项目名</label>
        <md-input v-model="projectName" readonly class="readonly_input"></md-input>
      </md-field>
      <md-field :md-toggle-password="false">
        <label>请输入您的账户密码</label>
        <md-input v-model="password" type="password"></md-input>
        
      </md-field>
      <p class="pay-tip"># 手续费1.0个币</p>
      <md-dialog-actions>
        <md-button class="md-primary" @click="onCancelCreate">取消</md-button>
        <md-button class="md-primary" @click="onConfirmCreate">创建</md-button>
      </md-dialog-actions>
    </md-dialog>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Watch } from "vue-property-decorator";
import { Getter, Mutation } from "vuex-class";
import { UserInterface } from "@utils/interface";
import {
  trim,
  containsBlank,
  isValidFileName,
  isValidPwd,
  getFileExtension,
  isUnSupportedType,
  isUnSupportedPackageType,
} from "@utils/index";
import { createProject, pullProject } from "@/api/project";
import { openLoading, closeLoading } from "@utils/share-variable";
import baseURL from "@utils/api-url";

@Component
export default class CreateProject extends Vue {
  projectName: string = "";
  selectCategory: string = "区块链";
  description: string = "";
  file: any = null;
  // 密码框
  showCreateDialog: boolean = false;
  password: string = "";
  readOnlyUserName: string = "";
  readOnlyAddress: string = "";

  // 仓库拉取
  isPull: boolean = false;
  selectRepoType: string = "gitee";
  repoList: Array<string> = ["gitee", "github", "gitlab"];
  projectToken: string = "";
  projectOwner: string = "";
  repoName: string = "";
  projectID: string = "";
  projectRef: string = "master";
  projectAddress: string = "https://gitee.com";

  isGitee: boolean = true;
  isGithub: boolean = false;
  isGitlab: boolean = false;

  @Getter("user") user!: UserInterface;
  @Getter("isLogin") isLogin!: boolean;
  @Getter("categoryList") categoryList!: Array<string>;
  @Mutation("SET_CATEGORY_LIST") setCategoryList: any;

  get userName(): string {
    return this.user.sUserName;
  }

  get avatar(): string {
    return `${baseURL}/user/User/getImage?sImagePath=${this.user.sAvatar}`;
  }

  created(): void {
    if (!this.isLogin) {
      this.$alert("您还没有登录");
      this.$router.push("/");
    }
    this.readOnlyUserName = this.user.sUserName;
    this.readOnlyAddress = this.user.sWalletAddress;

    if (!this.categoryList.length) {
      this.setCategoryList([
        "前端",
        "后台",
        "全栈",
        "运维",
        "区块链",
        "人工智能",
        "其他分类",
      ]);
    } else {
      console.log("列表存在啦");
    }
  }

  onPull(): void {
    if (!this.isPull) {
      return this.$alert("操作非法");
    }
    this.projectName = trim(this.projectName);
    this.selectRepoType = trim(this.selectRepoType);
    // 数据合法校验
    if (this.projectName === "") {
      return this.$alert("项目名称不能为空");
    }

    if (containsBlank(this.projectName)) {
      return this.$alert("项目名称不能包含空格");
    }

    if (this.projectName.length > 128) {
      return this.$alert("项目名称不能超过128位");
    }

    if (!isValidFileName(this.projectName)) {
      return this.$alert(
        `非法的项目名称，不能包含以下这些字符 & \` = * / \ ? " < > |`
      );
    }

    if (this.selectCategory === "") {
      return this.$alert("请选择项目分类");
    }

    if (this.selectRepoType === "") {
      return this.$alert("请选择仓库类型");
    }

    if (this.projectToken === "") {
      return this.$alert("请输入用户私钥");
    }
    if (this.projectRef === "") {
      return this.$alert("请输入要拉取的项目分支");
    }
    if (this.projectAddress === "") {
      return this.$alert("请输入要拉取的仓库地址");
    }
    if (this.selectRepoType == "gitee" && this.projectOwner === "") {
      return this.$alert("请输入创建gitee仓库的用户名");
    }
    if (this.selectRepoType == "gitee" && this.repoName === "") {
      return this.$alert("请输入gitee仓库的项目名");
    }
    if (this.selectRepoType == "github" && this.projectOwner === "") {
      return this.$alert("请输入github用户名");
    }
    if (this.selectRepoType == "github" && this.repoName === "") {
      return this.$alert("请输入相应的github项目名");
    }

    if (this.selectRepoType == "gitlab" && this.projectID === "") {
      return this.$alert("请输入正确的项目ID");
    }

    const formData = new FormData();
    formData.append("projectName", this.projectName);
    formData.append("projectDescription", this.description);
    formData.append("projectCategory", this.selectCategory);
    if (this.selectRepoType == "gitlab") {
      formData.append("repoType", "0");
    } else if (this.selectRepoType == "github") {
      formData.append("repoType", "1");
    } else if (this.selectRepoType == "gitee") {
      formData.append("repoType", "2");
    }
    formData.append("repoAddress", this.projectAddress);
    formData.append("repoToken", this.projectToken);
    formData.append("ref", this.projectRef);
    formData.append("projectID", this.projectID);
    formData.append("owner", this.projectOwner);
    formData.append("repoName", this.repoName);

    openLoading("正在创建中...");

    pullProject(formData)
      .then(() => {
        closeLoading();
        const url = `/detail/code?projectName=${encodeURIComponent(
          this.projectName
        )}&userName=${this.userName}`;
        this.$router.push(url);
      })
      .catch((err) => {
        closeLoading();
        this.$alert(err);
      });
  }

  onCreateStatus(): void {
    this.isPull = false;
  }
  onPullStatus(): void {
    this.isPull = true;
  }
  @Watch("isPull")
  onPullChange(newVal: boolean, oldVal: boolean) {
    // todo...
    if (newVal === false) {
      this.selectRepoType = "";
    } else {
      this.selectRepoType = "gitee";
    }
  }
  @Watch("selectRepoType")
  onselectRepoTypeChange() {
    // todo...
    this.projectID = "";
    this.projectOwner = "";
    this.repoName = "";
    console.log(this.selectRepoType);

    if (this.selectRepoType === "gitee") {
      this.isGitee = true;
      this.isGithub = false;
      this.isGitlab = false;
      this.projectAddress = "https://gitee.com";
    } else if (this.selectRepoType === "gitlab") {
      this.isGitee = false;
      this.isGithub = false;
      this.isGitlab = true;
      this.projectAddress = "https://gitlab.com";
    } else if (this.selectRepoType === "github") {
      this.isGitee = false;
      this.isGithub = true;
      this.isGitlab = false;
      this.projectAddress = "https://github.com";
    }
  }

  onConfirmCreate(): void{
    this.password = trim(this.password);

    // 数据合法校验
    if (this.password === "") {
      return this.$alert("密码不能为空");
    }
    if (!isValidPwd(this.password)) {
      return this.$alert(
        `密码需要由8-20位的字母和数字组成`
      );
    }

    const formData = new FormData();
    formData.append("sProjectName", this.projectName);
    formData.append("sDescription", this.description);
    formData.append("sCategoryName", this.selectCategory);
    formData.append("password", this.password);
    formData.append("upload-file", this.file);
    
    openLoading("正在创建中...");
    createProject(formData)
      .then(() => {
        closeLoading();
        // this.$alert('创建项目成功')
        const url = `/detail/code?projectName=${encodeURIComponent(
          this.projectName
        )}&userName=${this.userName}`;
        this.$router.push(url);
      })
      .catch((err) => {
        closeLoading();
        this.$alert(err);
      });
  }
  onCreate(): void {
    this.projectName = trim(this.projectName);

    // 数据合法校验
    if (this.projectName === "") {
      return this.$alert("项目名称不能为空");
    }

    if (containsBlank(this.projectName)) {
      return this.$alert("项目名称不能包含空格");
    }

    if (this.projectName.length > 128) {
      return this.$alert("项目名称不能超过128位");
    }

    if (!isValidFileName(this.projectName)) {
      return this.$alert(
        `非法的项目名称，不能包含以下这些字符 & \` = * / \ ? " < > |`
      );
    }

    if (this.selectCategory === "") {
      return this.$alert("请选择项目分类");
    }
    if (this.file) {
      const ext = getFileExtension(this.file.name);
      if (isUnSupportedType(ext)) {
        return this.$alert("暂不支持上传该类型的文件，后续会考虑支持");
      }
      if (isUnSupportedPackageType(ext)) {
        return this.$alert(
          "暂不支持该类型压缩包，后续会考虑支持，目前只支持zip和rar类型压缩包"
        );
      }
    }
    this.showCreateDialog = true
  }
  onCancelCreate(): void{
    this.showCreateDialog = false
  }

  onFileChange(fileList: any): void {
    console.log("文件变化：", fileList);
    if (!fileList.length) {
      console.log("没有选择文件");
      this.file = null;
      return;
    }
    this.file = fileList[0];
  }
}
</script>

<style lang="stylus" scoped>
.container {
  max-width: 500px;
}

.owner {
  display: flex;
  align-items: center;
}

.avatar {
  width: 25px;
  height: 25px;
  border: 1px solid #333;
}

.owner-wrapper {
  display: flex;
  align-items: center;

  img {
    margin-right: 3px;
  }
}

.field {
  margin-top: 10px;

  & > div {
    display: flex;
    align-items: center;
  }

  p {
    opacity: 0.44;
    margin-top: -18px;
  }
}

.md-icon {
  font-size: 18px !important;
  margin-top: 2px;
}
</style>

<style>
.md-tabs.md-theme-default .md-tabs-navigation {
  width: 90%;
  margin: 0 auto;
}

</style>

