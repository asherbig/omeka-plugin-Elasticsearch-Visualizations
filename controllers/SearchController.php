<?php

class Elasticsearch_SearchController extends Omeka_Controller_AbstractActionController {

    public function interceptorAction() {
        $q_string = http_build_query(['q'=> $this->_request->getParam('query')]);
        return $this->_helper->redirector->gotoUrl("/elasticsearch/search/index?$q_string");
    }

    public function indexAction() {
        $limit = get_option('per_page_public');
        $limit = isset($limit) ? $limit : 20;
        $page = $this->_request->page ? $this->_request->page : 1;
        $start = ($page - 1) * $limit;
        $user = $this->getCurrentUser();
        $query = $this->_getSearchParams();
        $sort = $this->_getSortParams();
        $highlight = true;

        // execute query
        $results = null;
        try {
            $results = Elasticsearch_Helper_Index::search([
                'query'             => $query,
                'offset'            => $start,
                'limit'             => $limit,
                'sort'              => $sort,
                'showNotPublic'     => $user && is_allowed('Items', 'showNotPublic'),
                'highlight'         => $highlight
            ]);

            Zend_Registry::set('pagination', [
                'per_page' => $limit,
                'page' => $page,
                'total_results' => $results['hits']['total']
            ]);
        } catch(Exception $e) {
            error_log($e->getMessage());
        }

        $graphData = $this->_generateGraphData($results);

        $this->view->assign('query', $query);
        $this->view->assign('results', $results);
        $this->view->assign('graphData', $graphData);
    }

    private function _generateGraphData($results) {
        if (!$results) {
            return null;
        } else {
            $hits = $results['hits']['hits'];
            $tagsToDocuments = array();
            $nodes = array();
            foreach($hits as $hit):
                $hitName = $hit['_source']['title'];
                $nodes[] = array(
                    "id" => $hitName,
                    "group" => 1
                );
                $tags = $hit['_source']['tags'];
                foreach($tags as $tagName):
                    if (!isset($tagsToDocuments[$tagName])) {
                        $tagsToDocuments[$tagName] = array($hitName);
                    } else {
                        $tagsToDocuments[$tagName][] = $hitName;
                    }
                endforeach;
            endforeach;
            $links = array();
            foreach($tagsToDocuments as $tagName => $documentsWithTag) {
                if (strpos($tagName, 'older') !== FALSE) {
                    for ($i = 0; $i < count($documentsWithTag); $i++) {
                        for ($j = $i + 1; $j < count($documentsWithTag); $j++) {
                            $links[] = array(
                                "source" => $documentsWithTag[$i],
                                "target" => $documentsWithTag[$j],
                                "value" => 1
                            );
                        }
                    }
                }
            }
            $graphData = array("nodes" => $nodes, "links" => $links);
            return json_encode($graphData);
        }
    }

    private function _getSearchParams() {
        $query = [
            'q'      => $this->_request->q, // search terms
            'facets' => []                  // facets to filter the search results
        ];
        foreach($this->_request->getQuery() as $k => $v) {
            if(strpos($k, 'facet_') === 0) {
                $query['facets'][substr($k, strlen('facet_'))] = $v;
            }
        }
        return $query;
    }

    private function _getSortParams() {
        $sort = [];
        if($this->_request->sort_field) {
            $sort['field'] = $this->_request->sort_field;
            if($this->_request->sort_dir) {
                $sort['dir'] = $this->_request->sort_dir;
            }
        }
        return $sort;
    }
}
